<?php

declare(strict_types=1);

namespace Datasets;

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
                Controller\DatasetsController::class => Controller\Factory\DatasetsControllerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'datasets' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
