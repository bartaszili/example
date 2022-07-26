<?php

declare(strict_types=1);

namespace Api\Handler\Factory;

use Api\Handler\InitSendHandler;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class InitSendHandlerFactory {
    public function __invoke(ContainerInterface $container): InitSendHandler {
        $config = $container->get('config');
        $tokens = [];
        if(isset($config['tokens']) && !empty($config['tokens'])) {
            $tokens = $config['tokens'];
        }
        return new InitSendHandler(
            $container->get(EntityManager::class),
            $tokens
        );
    }
}
