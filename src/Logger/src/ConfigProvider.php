<?php

declare(strict_types=1);

namespace Logger;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Laminas\Hydrator\ReflectionHydrator;
use Logger\Entity\Logs;
use Logger\Entity\Collection\LogsCollection;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;

class ConfigProvider {
    public function __invoke(): array {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'doctrine'     => $this->getDoctrineEntities(),
            MetadataMap::class => $this->getHalMetadataMap(),
        ];
    }

    private function getDependencies(): array {
        return [
            'delegators' => [
                Application::class => [RoutesDelegator::class]
            ],
            'invokables' => [],
            'factories'  => [
                Controller\LoggerController::class => Controller\Factory\LoggerControllerFactory::class,
                Handler\LoggerDeleteHandler::class => Handler\Factory\LoggerDeleteHandlerFactory::class,
                Handler\LoggerResetHandler::class => Handler\Factory\LoggerResetHandlerFactory::class,
                Handler\LoggerSearchHandler::class => Handler\Factory\LoggerSearchHandlerFactory::class,
                Handler\LoggerViewHandler::class => Handler\Factory\LoggerViewHandlerFactory::class,
            ],
        ];
    }

    private function getTemplates(): array {
        return [
            'paths' => [
                'logger' => [__DIR__ . '/../templates/'],
            ],
        ];
    }

    private function getDoctrineEntities(): array {
        return [
            'driver' => [
                'orm_default' => [
                    'class' => MappingDriverChain::class,
                    'drivers' => [
                        'Logger\Entity' => 'logger_entity',
                    ],
                ],
                'logger_entity' => [
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
                'resource_class' => Logs::class,
                'route' => 'logs.view',
                'extractor' => ReflectionHydrator::class,
            ],
            [
                '__class__' => RouteBasedCollectionMetadata::class,
                'collection_class' => LogsCollection::class,
                'collection_relation' => 'logs.view',
                'route' => 'logs.search',
            ],
        ];
    }
}
