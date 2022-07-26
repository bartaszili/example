<?php

declare(strict_types=1);

namespace Services\Helper\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\AreaHelper;
use Services\Helper\SlugifyHelper;

class AreaHelperFactory {
    public function __invoke(ContainerInterface $container): AreaHelper {
        $config = $container->get('config');
        $datasets = [];
        if(isset($config['datasets']) && !empty($config['datasets'])) {
            $datasets = $config['datasets'];
        }
        return new AreaHelper(
            $datasets,
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class)
        );
    }
}
