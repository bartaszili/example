<?php

declare(strict_types=1);

namespace Writer;

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
                Controller\WriterController::class => Controller\Factory\WriterControllerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'writer' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
