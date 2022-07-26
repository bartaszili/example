<?php

declare(strict_types=1);

namespace Services\Helper\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\DistanceHelper;
use Services\Helper\SlugifyHelper;

class DistanceHelperFactory {
    public function __invoke(ContainerInterface $container): DistanceHelper {
        $config = $container->get('config');
        $datasets = [];
        if(isset($config['datasets']) && !empty($config['datasets'])) {
            $datasets = $config['datasets'];
        }
        return new DistanceHelper(
            $datasets,
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class)
        );
    }
}
