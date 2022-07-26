<?php

declare(strict_types=1);

namespace Duplicates;

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
                Controller\DuplicatesController::class => Controller\Factory\DuplicatesControllerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'duplicates' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
