<?php

declare(strict_types=1);

namespace Commands\Command\Factory;

use Commands\Command\DatasetsCommand;
use Datasets\Controller\DatasetsController;
use Psr\Container\ContainerInterface;

class DatasetsCommandFactory {
    public function __invoke(ContainerInterface $container): DatasetsCommand {
        return new DatasetsCommand(
            $container->get(DatasetsController::class)
        );
    }
}
