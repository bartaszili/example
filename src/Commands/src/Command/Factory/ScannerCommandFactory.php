<?php

declare(strict_types=1);

namespace Commands\Command\Factory;

use Commands\Command\ScannerCommand;
use Psr\Container\ContainerInterface;
use Scanner\Controller\ScannerController;

class ScannerCommandFactory {
    public function __invoke(ContainerInterface $container): ScannerCommand {
        return new ScannerCommand(
            $container->get(ScannerController::class)
        );
    }
}
