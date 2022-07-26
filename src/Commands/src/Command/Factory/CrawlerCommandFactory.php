<?php

declare(strict_types=1);

namespace Commands\Command\Factory;

use Commands\Command\CrawlerCommand;
use Crawler\Controller\CrawlerController;
use Psr\Container\ContainerInterface;

class CrawlerCommandFactory {
    public function __invoke(ContainerInterface $container): CrawlerCommand {
        return new CrawlerCommand(
            $container->get(CrawlerController::class)
        );
    }
}
