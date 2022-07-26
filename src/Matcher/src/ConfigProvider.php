<?php

declare(strict_types=1);

namespace Matcher;

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
            'factories'  => [],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'matcher' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
