<?php

declare(strict_types=1);

namespace Logger\Handler\Factory;

use Doctrine\ORM\EntityManager;
use Logger\Handler\LoggerResetHandler;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerInterface;

class LoggerResetHandlerFactory {
    public function __invoke(ContainerInterface $container): LoggerResetHandler {
        $config = $container->get('config');
        $tokens = [];
        if(isset($config['tokens']) && !empty($config['tokens'])) {
            $tokens = $config['tokens'];
        }
        $page_size = 15;
        if(isset($config['page_size']) && !empty($config['page_size'])) {
            $page_size = $config['page_size'];
        }
        return new LoggerResetHandler(
            $container->get(EntityManager::class),
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $tokens,
            $page_size
        );
    }
}
