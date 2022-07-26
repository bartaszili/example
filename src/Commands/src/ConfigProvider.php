<?php

declare(strict_types=1);

namespace Commands;

class ConfigProvider {
    public function __invoke(): array {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    private function getDependencies(): array {
        return [
            'invokables' => [],
            'factories'  => [
                Command\CheckerCommand::class => Command\Factory\CheckerCommandFactory::class,
                Command\CrawlerCommand::class => Command\Factory\CrawlerCommandFactory::class,
                Command\DatasetsCommand::class => Command\Factory\DatasetsCommandFactory::class,
                Command\DuplicatesCommand::class => Command\Factory\DuplicatesCommandFactory::class,
                Command\ScannerCommand::class => Command\Factory\ScannerCommandFactory::class,
                Command\UpdaterCommand::class => Command\Factory\UpdaterCommandFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'commands' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
