<?php

declare(strict_types=1);

/**
 * FastRoute configuration.
 */

use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => FastRouteRouter::class,
        ],
    ],
    'router' => [
        'fastroute' => [
             // Enable caching support:
            'cache_enabled' => false,
             // Optional (but recommended) cache file path:
            'cache_file'    => 'data/cache/fastroute.php.cache',
        ],
    ],
];

