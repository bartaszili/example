<?php

declare(strict_types=1);

namespace Services\Helper\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\TypeHelper;
use Services\Helper\SlugifyHelper;

class TypeHelperFactory {
    public function __invoke(ContainerInterface $container): TypeHelper {
        $config = $container->get('config');
        $datasets = [];
        if(isset($config['datasets']) && !empty($config['datasets'])) {
            $datasets = $config['datasets'];
        }
        return new TypeHelper(
            $datasets,
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class)
        );
    }
}
