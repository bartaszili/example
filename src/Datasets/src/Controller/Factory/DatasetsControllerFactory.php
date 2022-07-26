<?php

declare(strict_types=1);

namespace Datasets\Controller\Factory;

use Datasets\Controller\DatasetsController;
use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\AddressHelper;
use Services\Helper\SlugifyHelper;

class DatasetsControllerFactory {
    public function __invoke(ContainerInterface $container): DatasetsController {
        $config = $container->get('config');
        $datasets = [];
        if(isset($config['datasets']) && !empty($config['datasets'])) {
            $datasets = $config['datasets'];
        }
        return new DatasetsController(
            $container->get(AddressHelper::class),
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class),
            $datasets
        );
    }
}
