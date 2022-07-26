<?php

declare(strict_types=1);

namespace TargetReality\Controller\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\AddressHelper;
use Services\Helper\AreaHelper;
use Services\Helper\CategoryHelper;
use Services\Helper\CountryFinderHelper;
use Services\Helper\CurrencyHelper;
use Services\Helper\SlugifyHelper;
use Services\Helper\TypeHelper;
use TargetReality\Controller\MatcherRealityController;

class MatcherRealityControllerFactory {
    public function __invoke(ContainerInterface $container): MatcherRealityController {
        return new MatcherRealityController(
            $container->get(AddressHelper::class),
            $container->get(AreaHelper::class),
            $container->get(CategoryHelper::class),
            $container->get(CountryFinderHelper::class),
            $container->get(CurrencyHelper::class),
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class),
            $container->get(TypeHelper::class)
        );
    }
}
