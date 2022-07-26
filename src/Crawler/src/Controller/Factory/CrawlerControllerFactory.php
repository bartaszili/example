<?php

declare(strict_types=1);

namespace Crawler\Controller\Factory;

use Crawler\Controller\CrawlerController;
use Psr\Container\ContainerInterface;

class CrawlerControllerFactory {
    public function __invoke(ContainerInterface $container): CrawlerController {
        $config = $container->get('config');
        $crawler_targets = [];
        if(isset($config['crawler_targets']) && !empty($config['crawler_targets'])) {
            $crawler_targets = $config['crawler_targets'];
        }
        return new CrawlerController(
            $container,
            $crawler_targets
        );
    }
}
