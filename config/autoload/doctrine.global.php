<?php

declare(strict_types=1);

/**
 * Doctrine configuration
 */

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use DoctrineExtensions\Query\Mysql;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'driver' => 'pdo_mysql',
                    'host' => '',
                    'port' => '',
                    'dbname' => '',
                    'user' => '',
                    'password' => '',
                    'charset'  => 'utf8mb4',
                    'collation'=> 'utf8mb4_unicode_ci',
                ],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'app_entity',
                ],
            ],
            'app_entity' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    getcwd().'/src/App/src/Entity',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'datetime_functions' => [
                    'date_format' => Mysql\DateFormat::class,
                ],
                'numeric_functions' => [
                    'acos' => Mysql\Asin::class,
                    'cos' => Mysql\Cos::class,
                    'radians' => Mysql\Radians::class,
                    'sin' => Mysql\Sin::class,
                ],
                'string_functions' => [
                    'ifnull' => Mysql\IfNull::class,
                    'match' => Mysql\MatchAgainst::class,
                ],
            ]
        ],
    ],
];
