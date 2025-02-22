<?php

declare(strict_types=1);

use Slim\App;
use Monolog\Logger;
use App\SessionHandler;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use App\UseCase\CreateTransaction;
use Monolog\Handler\StreamHandler;
use App\Infra\Session\ArrayHandler;
use Monolog\Handler\ErrorLogHandler;
use Psr\Container\ContainerInterface;
use App\Infra\Store\Dbal\TransactionStore;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;
use App\Infra\Store\Dbal\TransactionRepositoryFactory;
use App\Transaction\Store\TransactionRepositoryInterface;

$getSettings = fn (ContainerInterface $container) => (array) $container->get('settings');

return static function (ContainerBuilder $containerBuilder, array $settings) {
    $containerBuilder->addDefinitions([
        'settings' => $settings,

        Connection::class => function (ContainerInterface $container) use ($settings) {
            $connection = $settings['database']['db'];

            return DriverManager::getConnection($connection);
        },

        TransactionRepositoryInterface::class => function (ContainerInterface $container
        ): TransactionRepositoryInterface {
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);

            /** @var Connection $connection */
            $connection = $container->get(Connection::class);

            return new TransactionStore($logger, $connection);
        },

        CreateTransaction::class => function (ContainerInterface $container) {
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);

            /** @var Connection $connection */
            $connection = $container->get(Connection::class);

            $repositoryFactory = new TransactionRepositoryFactory($logger, $connection);

            return new CreateTransaction($logger, $repositoryFactory);
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
    ]);
};
