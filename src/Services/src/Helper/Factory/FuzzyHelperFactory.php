<?php

declare(strict_types=1);

namespace Services\Helper\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\FuzzyHelper;
use Services\Helper\SlugifyHelper;

class FuzzyHelperFactory {
    public function __invoke(ContainerInterface $container): FuzzyHelper {
        return new FuzzyHelper(
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class)
        );
    }
}
