<?php

declare(strict_types=1);

namespace Services;

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
                Helper\AddressHelper::class => Helper\Factory\AddressHelperFactory::class,
                Helper\AreaHelper::class => Helper\Factory\AreaHelperFactory::class,
                Helper\CategoryHelper::class => Helper\Factory\CategoryHelperFactory::class,
                Helper\CountryFinderHelper::class => Helper\Factory\CountryFinderHelperFactory::class,
                Helper\CurrencyHelper::class => Helper\Factory\CurrencyHelperFactory::class,
                Helper\DistanceHelper::class => Helper\Factory\DistanceHelperFactory::class,
                Helper\FuzzyHelper::class => Helper\Factory\FuzzyHelperFactory::class,
                Helper\SlugifyHelper::class => Helper\Factory\SlugifyHelperFactory::class,
                Helper\StopWordsHelper::class => Helper\Factory\StopWordsHelperFactory::class,
                Helper\TypeHelper::class => Helper\Factory\TypeHelperFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'services' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
