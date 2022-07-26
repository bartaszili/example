<?php

declare(strict_types=1);

namespace Parser\Controller\Factory;

use Logger\Controller\LoggerController;
use Parser\Controller\ParserController;
use Psr\Container\ContainerInterface;
use Services\Helper\SlugifyHelper;

class ParserControllerFactory {
    public function __invoke(ContainerInterface $container): ParserController {
        return new ParserController(
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class)
        );
    }
}
