<?php

declare(strict_types=1);

namespace Duplicates\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Duplicates\Controller\DuplicatesController;
use Logger\Controller\LoggerController;
use Psr\Container\ContainerInterface;
use Services\Helper\FuzzyHelper;
use Services\Helper\SlugifyHelper;
use Services\Helper\StopWordsHelper;

class DuplicatesControllerFactory {
    public function __invoke(ContainerInterface $container): DuplicatesController {
        $config = $container->get('config');
        $duplicates = [];
        if(isset($config['duplicates'])) {
            $duplicates = $config['duplicates'];
        }
        $writer = [];
        if(isset($config['writer'])) {
            $writer = $config['writer'];
        }
        return new DuplicatesController(
            $duplicates,
            $container->get(EntityManager::class),
            $container->get(FuzzyHelper::class),
            $container->get(LoggerController::class),
            $container->get(SlugifyHelper::class),
            $container->get(StopWordsHelper::class),
            $writer
        );
    }
}
