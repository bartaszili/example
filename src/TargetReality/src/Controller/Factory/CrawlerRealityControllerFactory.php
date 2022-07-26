<?php

declare(strict_types=1);

namespace TargetReality\Controller\Factory;

use Logger\Controller\LoggerController;
use Parser\Controller\ParserController;
use Psr\Container\ContainerInterface;
use Scanner\Controller\ScannerController;
use TargetReality\Controller\CrawlerRealityController;
use Writer\Controller\WriterController;

class CrawlerRealityControllerFactory {
    public function __invoke(ContainerInterface $container): CrawlerRealityController {
        $config = $container->get('config');
        $options = [];
        if(isset($config['crawler_targets']['reality']) && !empty($config['crawler_targets']['reality'])) {
            $options = $config['crawler_targets']['reality'];
        }
        $proxy_settings = [];
        if(isset($config['socks5_proxy']) && !empty($config['socks5_proxy'])) {
            $proxy_settings = $config['socks5_proxy'];
        }
        $target_name = '';
        if(isset($config['crawler_targets']['reality'])) {
            $target_name = 'reality';
        }
        return new CrawlerRealityController(
            $config,
            $container->get(LoggerController::class),
            $options,
            $container->get(ParserController::class),
            $proxy_settings,
            $container->get(ScannerController::class),
            $target_name,
            $container->get(WriterController::class)
        );
    }
}
