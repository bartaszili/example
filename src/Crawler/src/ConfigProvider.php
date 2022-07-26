<?php

declare(strict_types=1);

namespace Crawler;

class ConfigProvider {
    public function __invoke(): array {
        return [
            'dependencies'    => $this->getDependencies(),
            'templates'       => $this->getTemplates(),
        ];
    }

    private function getDependencies(): array {
        return [
            'invokables' => [],
            'factories'  => [
                Controller\CrawlerController::class => Controller\Factory\CrawlerControllerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'crawler' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
