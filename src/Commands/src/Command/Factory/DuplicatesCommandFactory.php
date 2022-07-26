<?php

declare(strict_types=1);

namespace Commands\Command\Factory;

use Commands\Command\DuplicatesCommand;
use Duplicates\Controller\DuplicatesController;
use Psr\Container\ContainerInterface;

class DuplicatesCommandFactory {
    public function __invoke(ContainerInterface $container): DuplicatesCommand {
        return new DuplicatesCommand(
            $container->get(DuplicatesController::class)
        );
    }
}
