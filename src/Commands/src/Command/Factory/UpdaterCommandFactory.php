<?php

declare(strict_types=1);

namespace Commands\Command\Factory;

use Commands\Command\UpdaterCommand;
use Crawler\Controller\CrawlerController;
use Psr\Container\ContainerInterface;

class UpdaterCommandFactory {
    public function __invoke(ContainerInterface $container): UpdaterCommand {
        return new UpdaterCommand(
            $container->get(CrawlerController::class)
        );
    }
}
