<?php

declare(strict_types=1);

namespace Logger\Handler\Factory;

use Doctrine\ORM\EntityManager;
use Logger\Handler\LoggerViewHandler;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerInterface;

class LoggerViewHandlerFactory {
    public function __invoke(ContainerInterface $container): LoggerViewHandler {
        $config = $container->get('config');
        $tokens = [];
        if(isset($config['tokens']) && !empty($config['tokens'])) {
            $tokens = $config['tokens'];
        }
        $page_size = 15;
        if(isset($config['page_size']) && !empty($config['page_size'])) {
            $page_size = $config['page_size'];
        }
        return new LoggerViewHandler(
            $container->get(EntityManager::class),
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $tokens,
            $page_size
        );
    }
}
