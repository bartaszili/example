<?php

declare(strict_types=1);

namespace Parser;

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
                Controller\ParserController::class => Controller\Factory\ParserControllerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'parser' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
