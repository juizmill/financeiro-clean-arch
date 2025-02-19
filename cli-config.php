#!/usr/bin/env php
<?php

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\Console\Application;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;

require_once 'vendor/autoload.php';

$envFile = __DIR__ . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $dotenv = new Dotenv();
    $dotenv->usePutenv()->load($envFile);
}

$settings = require 'config/settings.php';

$connection = DriverManager::getConnection($settings()['database']['db']);
$config = new ConfigurationArray([
    'migrations_paths' => [
        'Migrations' => __DIR__ . '/database/migrations',
    ],
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
        'version_column_name' => 'version',
        'executed_at_column_name' => 'executed_at',
    ],
]);

$dependencyFactory = DependencyFactory::fromConnection($config, new ExistingConnection($connection));

$cli = new Application('Doctrine Migrations');
$cli->setCatchExceptions(true);

$cli->addCommands([
    new Command\DumpSchemaCommand($dependencyFactory),
    new Command\ExecuteCommand($dependencyFactory),
    new Command\GenerateCommand($dependencyFactory),
    new Command\LatestCommand($dependencyFactory),
    new Command\ListCommand($dependencyFactory),
    new Command\MigrateCommand($dependencyFactory),
    new Command\RollupCommand($dependencyFactory),
    new Command\StatusCommand($dependencyFactory),
    new Command\SyncMetadataCommand($dependencyFactory),
    new Command\VersionCommand($dependencyFactory),
]);

$cli->run();
