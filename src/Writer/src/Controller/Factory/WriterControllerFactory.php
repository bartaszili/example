<?php

declare(strict_types=1);

namespace Writer\Controller\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Writer\Controller\WriterController;

class WriterControllerFactory {
    public function __invoke(ContainerInterface $container): WriterController {
        $config = $container->get('config');
        $writer = [];
        if(!empty($config['writer'])) {
            $writer = $config['writer'];
        }
        return new WriterController(
            $container->get(LoggerController::class),
            $writer
        );
    }
}
