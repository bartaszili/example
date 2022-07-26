<?php

declare(strict_types=1);

namespace Scanner;

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
                Controller\ScannerController::class => Controller\Factory\ScannerControllerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'scanner' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
