<?php

declare(strict_types=1);

namespace Services\Helper\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\CountryFinderHelper;
use Services\Helper\SlugifyHelper;

class CountryFinderHelperFactory {
    public function __invoke(ContainerInterface $container): CountryFinderHelper {
        $config = $container->get('config');
        $datasets = [];
        if(isset($config['datasets']) && !empty($config['datasets'])) {
            $datasets = $config['datasets'];
        }
        return new CountryFinderHelper(
            $datasets,
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class)
        );
    }
}
