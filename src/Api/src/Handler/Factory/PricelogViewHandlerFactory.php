<?php

declare(strict_types=1);

namespace Api\Handler\Factory;

use Api\Handler\PricelogViewHandler;
use Doctrine\ORM\EntityManager;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerInterface;

class PricelogViewHandlerFactory {
    public function __invoke(ContainerInterface $container): PricelogViewHandler {
        $config = $container->get('config');
        $tokens = [];
        if(isset($config['tokens']) && !empty($config['tokens'])) {
            $tokens = $config['tokens'];
        }
        $page_size = 15;
        if(isset($config['page_size']) && !empty($config['page_size'])) {
            $page_size = $config['page_size'];
        }
        return new PricelogViewHandler(
            $container->get(EntityManager::class),
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $tokens,
            $page_size
        );
    }
}
