<?php

declare(strict_types=1);

namespace Api;

use Api\Entity\Property;
use Api\Entity\Collection\PropertyCollection;
use Api\Entity\Pricelog;
use Api\Entity\Collection\PricelogCollection;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Laminas\Hydrator\ReflectionHydrator;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;

class ConfigProvider {
    public function __invoke(): array {
        return [
            'dependencies'     => $this->getDependencies(),
            'templates'        => $this->getTemplates(),
            'doctrine'         => $this->getDoctrineEntities(),
            MetadataMap::class => $this->getHalMetadataMap(),
        ];
    }

    private function getDependencies(): array {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class
                ]
            ],
            'invokables' => [],
            'factories'  => [
                Handler\InitSendHandler::class => Handler\Factory\InitSendHandlerFactory::class,
                Handler\PricelogGraphHandler::class => Handler\Factory\PricelogGraphHandlerFactory::class,
                Handler\PricelogSearchHandler::class => Handler\Factory\PricelogSearchHandlerFactory::class,
                Handler\PricelogViewHandler::class => Handler\Factory\PricelogViewHandlerFactory::class,
                Handler\PropertiesSearchHandler::class => Handler\Factory\PropertiesSearchHandlerFactory::class,
                Handler\PropertiesViewHandler::class => Handler\Factory\PropertiesViewHandlerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'api' => [__DIR__ . '/../templates/'],
            ],
        ];
    }

    private function getDoctrineEntities(): array {
        return [
            'driver' => [
                'orm_default' => [
                    'class' => MappingDriverChain::class,
                    'drivers' => [
                        'Api\Entity' => 'api_entity',
                    ],
                ],
                'api_entity' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
    }

    private function getHalMetadataMap() {
        return [
            [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => Pricelog::class,
                'route' => 'pricelog.view',
                'extractor' => ReflectionHydrator::class,
            ],
            [
                '__class__' => RouteBasedCollectionMetadata::class,
                'collection_class' => PricelogCollection::class,
                'collection_relation' => 'pricelog',
                'route' => 'pricelog.search',
            ],
            [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => Property::class,
                'route' => 'properties.view',
                'extractor' => ReflectionHydrator::class,
            ],
            [
                '__class__' => RouteBasedCollectionMetadata::class,
                'collection_class' => PropertyCollection::class,
                'collection_relation' => 'properties',
                'route' => 'properties.search',
            ],
        ];
    }
}
