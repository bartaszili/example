<?php

declare(strict_types=1);

namespace Commands\Command\Factory;

use Commands\Command\CheckerCommand;
use Crawler\Controller\CrawlerController;
use Psr\Container\ContainerInterface;

class CheckerCommandFactory {
    public function __invoke(ContainerInterface $container): CheckerCommand {
        return new CheckerCommand(
            $container->get(CrawlerController::class)
        );
    }
}
