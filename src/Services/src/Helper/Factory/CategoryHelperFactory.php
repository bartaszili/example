<?php

declare(strict_types=1);

namespace Services\Helper\Factory;

use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\CategoryHelper;
use Services\Helper\SlugifyHelper;

class CategoryHelperFactory {
    public function __invoke(ContainerInterface $container): CategoryHelper {
        $config = $container->get('config');
        $datasets = [];
        if(isset($config['datasets']) && !empty($config['datasets'])) {
            $datasets = $config['datasets'];
        }
        return new CategoryHelper(
            $datasets,
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class)
        );
    }
}
