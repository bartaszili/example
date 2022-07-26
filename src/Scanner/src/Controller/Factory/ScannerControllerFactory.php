<?php

declare(strict_types=1);

namespace Scanner\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Logger\Controller\LoggerController;
use Parser\Controller\ParserController;
use Psr\Container\ContainerInterface;
use Scanner\Controller\ScannerController;

class ScannerControllerFactory {
    public function __invoke(ContainerInterface $container): ScannerController {
        $config = $container->get('config');
        $scanner = [];
        if(isset($config['scanner'])) {
            $scanner = $config['scanner'];
        }
        $targets = [];
        if(isset($config['crawler_targets'])) {
            $targets = $config['crawler_targets'];
        }
        $writer = [];
        if(isset($config['writer'])) {
            $writer = $config['writer'];
        }
        return new ScannerController(
            $container,
            $container->get(EntityManager::class),
            $container->get(LoggerController::class),
            $container->get(ParserController::class),
            $scanner,
            $targets,
            $writer
        );
    }
}
