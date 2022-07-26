<?php

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use Commands\Command\CheckerCommand;
use Commands\Command\CrawlerCommand;
use Commands\Command\DatasetsCommand;
use Commands\Command\DuplicatesCommand;
use Commands\Command\ScannerCommand;
use Commands\Command\UpdaterCommand;
use Symfony\Component\Console\Application;

(function () {
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';
    $app = new Application();
    $app->add($container->get(CheckerCommand::class));
    $app->add($container->get(CrawlerCommand::class));
    $app->add($container->get(DatasetsCommand::class));
    $app->add($container->get(DuplicatesCommand::class));
    $app->add($container->get(ScannerCommand::class));
    $app->add($container->get(UpdaterCommand::class));
    $app->run();
})();
