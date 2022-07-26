<?php

declare(strict_types=1);

namespace Logger;

use Mezzio\Application;
use Psr\Container\ContainerInterface;

class RoutesDelegator {
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback) {
        $app = $callback();

        // Delete
        $app->post(
            '/logs/delete/{id:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}[/]',
            Handler\LoggerDeleteHandler::class,
            'logs.delete'
        );
        $app->post(
            '/logs/reset[/]',
            Handler\LoggerResetHandler::class,
            'logs.reset'
        );

        // Search
        $app->post(
            "/logs/search/[?page={page:\d+}]",
            Handler\LoggerSearchHandler::class,
            'logs.search'
        );

        // View by ID
        $app->post(
            '/logs/view/{id:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}[/]',
            Handler\LoggerViewHandler::class,
            'logs.view'
        );
        return $app;
    }
}
