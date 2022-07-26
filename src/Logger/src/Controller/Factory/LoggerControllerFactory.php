<?php

declare(strict_types=1);

namespace Logger\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;

class LoggerControllerFactory {
    public function __invoke(ContainerInterface $container): LoggerController {
        $config = $container->get('config');
        $debug = [];
        if(!empty($config['debugger'])) { $debug = $config['debugger']; }
        $logger = [];
        if(!empty($config['logger'])) { $logger = $config['logger']; }
        $permissions = [];
        if(!empty($config['permissions'])) { $permissions = $config['permissions']; }
        return new LoggerController(
            $container->get(EntityManager::class),
            $debug,
            $logger,
            $permissions
        );
    }
}
