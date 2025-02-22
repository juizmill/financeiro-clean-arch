<?php

declare(strict_types=1);

namespace App\UserInterface\Web;

use Closure;
use Slim\App;
use DI\Container;
use ReflectionClass;
use ReflectionMethod;
use DI\ContainerBuilder;
use ReflectionException;
use DI\NotFoundException;
use DI\DependencyException;
use Psr\Log\LoggerInterface;
use App\Transaction\UserInterface;
use Slim\Handlers\Strategies\RequestHandler;
use Composer\ClassMapGenerator\ClassMapGenerator;
use App\UserInterface\Web\Handler\AbstractHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

readonly class FinanceSystemInterface implements UserInterface
{
    /**
     * @var ContainerBuilder<Container>
     */
    private ContainerBuilder $containerBuilder;

    /**
     * @param Closure                          $settings         the settings configuration
     * @param Closure|null                     $dependencies     the dependencies configuration
     * @param Closure|null                     $middleware       the middleware configuration
     * @param ContainerBuilder<Container>|null $containerBuilder the container builder
     */
    public function __construct(
        private Closure $settings,
        private ?Closure $dependencies = null,
        private ?Closure $middleware = null,
        ?ContainerBuilder $containerBuilder = null
    ) {
        $this->containerBuilder = $containerBuilder ?? new ContainerBuilder();
    }

    /**
     * Executes the configuration process for the application.
     *
     * This method initializes the application's dependency injection
     * container and middleware by invoking the configure method
     * with the provided dependencies and middleware closures.
     *
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function run(): void
    {
        $this->configure($this->dependencies, $this->middleware)->run();
    }

    /**
     * Configures the container and application.
     *
     * This method sets up the dependency injection container with the provided
     * definitions and executes additional configurations for dependencies and
     * middleware. It builds the container, retrieves the application instance,
     * and sets the default invocation strategy. Finally, it configures route
     * handlers using the application and logger instances.
     *
     * @param  Closure|null        $dependencies optional closure to configure dependencies
     * @param  Closure|null        $middleware   optional closure to configure middleware
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws DependencyException
     */
    private function configure(?Closure $dependencies, ?Closure $middleware): App
    {
        /** @var array{di_compilation_path?:string, definitions?:array} $settings */
        $settings = ($this->settings)(); // @phpstan-ignore-line

        $this->containerBuilder->addDefinitions($settings['definitions'] ?? []);

        if ($dependencies !== null) {
            $dependencies($this->containerBuilder, $settings);
        }

        $container = $this->containerBuilder->build();

        /** @var App<ContainerInterface> $app */
        $app = $container->get(App::class);
        $app->getRouteCollector()->setDefaultInvocationStrategy(
            new RequestHandler(true)
        );

        if ($middleware !== null) {
            $middleware($app);
        }

        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);
        $this->configureHandlers($app, $logger);

        return $app;
    }

    /**
     * Configures handlers in the application.
     *
     * This method registers Slim routes using the `Route` attribute on methods of
     * classes in the `Handler` namespace.
     *
     * @param  App<ContainerInterface> $app    The Slim application to register the routes with
     * @param  LoggerInterface         $logger The logger to use for debugging
     * @throws ReflectionException
     */
    private function configureHandlers(App $app, LoggerInterface $logger): void
    {
        $handlerMap = ClassMapGenerator::createMap(__DIR__ . '/Handler');
        foreach ($handlerMap as $handlerClass => $handlerFile) {
            $logger->debug('Registering handler: ', [
                'class' => $handlerClass,
                'file' => $handlerFile,
            ]);

            $reflectionClass = new ReflectionClass($handlerClass);
            if ($reflectionClass->isSubclassOf(AbstractHandler::class)) {
                foreach ($reflectionClass->getMethods() as $method) {
                    $this->discoverRoutes($app, $method, $logger);
                }
            }
        }
    }

    /**
     * Discover routes from the given method.
     *
     * This method discovers and registers Slim routes from the given method.
     * It does this by iterating over the method's attributes of type
     * {@see Route}, and for each one, it calls {@see registerRoute} to
     * register the route.
     *
     * @param App<ContainerInterface> $app              The Slim app to register the routes with
     * @param LoggerInterface         $logger           The logger to use for debugging
     * @param ReflectionMethod        $reflectionMethod The method to discover routes from
     */
    private function discoverRoutes(App $app, ReflectionMethod $reflectionMethod, LoggerInterface $logger): void
    {
        $logger->debug(
            'Discovering routes',
            [
                'method' => $reflectionMethod->getName(),
                'class' => $reflectionMethod->getDeclaringClass()->getName(),
            ]
        );

        foreach ($reflectionMethod->getAttributes(Route::class) as $attribute) {
            $arguments = $attribute->getArguments();

            $this->registerRoute(
                $app,
                $logger,
                $arguments[0],
                $arguments[1],
                [
                    $reflectionMethod->getDeclaringClass()->getName(),
                    $reflectionMethod->getName(),
                ],
                $arguments[2]
            );
        }
    }

    /**
     * Registers a route in the given Slim app.
     *
     * This method is called by {@see discoverRoutes} for each route
     * discovered in the given class.
     *
     * @param App<ContainerInterface> $app      The Slim app to register the route with
     * @param LoggerInterface         $logger   The logger to use for debugging
     * @param array|Method            $method   The HTTP methods to register the route for
     * @param string                  $pattern  The route pattern to register
     * @param ?string                 $alias    The route name to register
     * @param callable|array|string   $callable The callable to register as the route handler
     * @phpstan-ignore-next-line
     */
    private function registerRoute(
        App $app,
        LoggerInterface $logger,
        array|Method $method,
        string $pattern,
        callable|array|string $callable,
        ?string $alias
    ): void {
        if (! is_array($method)) {
            $method = [$method];
        }

        $methods = array_map(fn (Method $method) => $method->value, $method);

        $logger->debug(
            'Registering route',
            [
                $alias => [
                    'pattern' => $pattern,
                    'methods' => $methods,
                ],
            ]
        );

        $app->map(
            $methods,
            $pattern,
            $callable // @phpstan-ignore-line
        );
    }
}
