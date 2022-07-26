<?php

namespace Api;

use Psr\Container\ContainerInterface;
use Mezzio\Application;

class RoutesDelegator {
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback) {
        /** @var $app Application */
        $app = $callback();

        // Graph
        $app->post(
            "/api/pricelog/graph[/]",
            Handler\PricelogGraphHandler::class,
            'pricelog.graph'
        );

        // Search
        $app->post(
            "/api/pricelog/search/[?page={page:\d+}]",
            Handler\PricelogSearchHandler::class,
            'pricelog.search'
        );
        $app->post(
            "/api/properties/search/[?page={page:\d+}]",
            Handler\PropertiesSearchHandler::class,
            'properties.search'
        );

        // Send
        $app->post(
            "/api/init/send[/]",
            Handler\InitSendHandler::class,
            'init.send'
        );

        // View by ID
        $app->post(
            '/api/pricelog/view/{id:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}[/]',
            Handler\PricelogViewHandler::class,
            'pricelog.view'
        );
        $app->post(
            '/api/properties/view/{id:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}[/]',
            Handler\PropertiesViewHandler::class,
            'properties.view'
        );

        return $app;
    }
}
