<?php

declare(strict_types=1);

use Slim\App;
use Monolog\Logger;
use Twig\Environment;
use Twig\TwigFunction;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;
use Monolog\Handler\StreamHandler;
use App\Transaction\SessionHandler;
use Monolog\Handler\ErrorLogHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;
use App\Transaction\UseCase\CreateTransaction;
use App\Transaction\Infra\Session\ArrayHandler;
use App\Transaction\UserInterface\Web\Twig\AssetFunction;
use App\Transaction\Infra\Store\Memory\TransactionStoreMemory;
use App\Transaction\Domain\Store\TransactionRepositoryInterface;

$getSettings = fn (ContainerInterface $container) => (array) $container->get('settings');

return static function (ContainerBuilder $containerBuilder, array $settings) {
    $containerBuilder->addDefinitions([
        'settings' => $settings,

        TransactionRepositoryInterface::class => function (ContainerInterface $container) {
            return new TransactionStoreMemory();
        },

        CreateTransaction::class => function (ContainerInterface $container) {
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);

            /** @var TransactionRepositoryInterface $repository */
            $repository = $container->get(TransactionRepositoryInterface::class);

            return new CreateTransaction($logger, $repository);
        },

        App::class => function (ContainerInterface $container) {
            AppFactory::setContainer($container);
            $app = AppFactory::create();

            /** @var SessionHandler $session */
            $session = $container->get(SessionHandler::class);
            $session->start();

            return $app;
        },

        LoggerInterface::class => function () use ($settings) {
            $name = $settings['logger']['name'] ?? 'app';
            $logger = new Logger($name);
            $formatter = new Monolog\Formatter\LineFormatter(
                "[%datetime%] [%level_name%] %message% %context% %extra%\n",
                'd/m/Y H:i:s',
                true,
                true
            );

            $handler = new StreamHandler($settings['logger']['path'], $settings['logger']['level']);
            $logger->pushHandler($handler);

            $handler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $settings['logger']['level']);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        },

        SessionHandler::class => function (ContainerInterface $container) {
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);

            return new ArrayHandler($logger);
        },

        ServerRequestInterface::class => function () {
            return ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();
        },

        Environment::class => function () use ($settings) {
            $mainTemplatesDir = '../resources/templates';
            $loader = new FilesystemLoader($mainTemplatesDir);

            $twig = new Environment($loader, ['debug' => $settings['twig_debug'] ?? false]);
            $twig->addFunction(new TwigFunction('public_path', [new AssetFunction(), 'publicPath']));

            if ($settings['app']['env'] !== 'PRODUCTION') {
                $twig->enableDebug();
            }

            if ($settings['app']['env'] === 'PRODUCTION' && $settings['twig_cache'] !== '') {
                $twig->setCache($settings['twig_cache']);
            }

            return $twig;
        },
    ]);
};
