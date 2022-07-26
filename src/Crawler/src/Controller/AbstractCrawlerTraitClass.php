<?php

declare(strict_types=1);

namespace Crawler\Controller;

use GuzzleHttp\Client;
use Logger\Controller\LoggerController;
use Parser\Controller\ParserController;
use Psr\Http\Message\UriInterface;
use Scanner\Controller\ScannerController;
use Writer\Controller\WriterController;
use \Exception;

trait AbstractCrawlerTraitClass {
    use AbstractCrawlerTraitMethods;

    private $config = [];
    private Client $client;
    private $exit = false;
    private $filename = '';
    private LoggerController $logger;
    private $options = [];
    private $paginator_items = null;
    private $paginator_pages = null;
    private $paginator_total = null;
    private ParserController $parser;
    private $proxy_settings = [];
    private ScannerController $scanner;
    private $session_id = '';
    private int $shift = 0;
    private $target_name = '';
    private $try_again = 3;
    private $try_again_count = 1;
    private $try_again_delay = 400;
    private $try_again_loop = true;
    private UriInterface $uri;
    private WriterController $writer;

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getClient(): Client { return $this->client; }
    private function setClient(Client $input): void { $this->client = $input; }

    private function getConfig(): array { return $this->config; }
    private function setConfig(array $input): void { $this->config = $input; }

    private function getExit(): bool { return $this->exit; }
    private function setExit(bool $input): void { $this->exit = $input; }

    private function getFilename(): string { return $this->filename; }
    private function setFilename(string $input): void { $this->filename = $input; }

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    private function getOptions(): array { return $this->options; }
    public function setOptions(array $input = []) { $this->options = $input; }

    private function getParser(): ParserController { return $this->parser; }
    private function setParser(ParserController $input): void { $this->parser = $input; }

    private function getProxySettings(): array { return $this->proxySettings; }
    private function setProxySettings(array $input): void { $this->proxySettings = $input; }

    private function getScanner(): ScannerController { return $this->scanner; }
    private function setScanner(ScannerController $input): void { $this->scanner = $input; }

    private function getSessionId(): string { return $this->session_id; }
    private function setSessionId(string $input): void { $this->session_id = $input; }

    private function getShift(): int { return $this->shift; }
    private function setShift(int $input): void { $this->shift = $input; }

    private function getTargetName(): string { return $this->target_name; }
    private function setTargetName(string $input): void { $this->target_name = $input; }

    private function getUri(): UriInterface { return $this->uri; }
    private function setUri(UriInterface $input): void { $this->uri = $input; }

    private function getWriter(): WriterController { return $this->writer; }
    private function setWriter(WriterController $input): void { $this->writer = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    /**
     * This checks configuration options.
     * To be passed without Exception.
     *
     * @throws Exception With detailed problem description.
     */
    private function configCheck(): void {
        if(empty($this->getConfig())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing main configuration"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing main configuration");
        }
        if(!isset($this->getConfig()['crawler']) || empty($this->getConfig()['crawler'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawler` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawler` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getConfig()['crawler']['extension']) || empty($this->getConfig()['crawler']['extension'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `extension` attribute from `crawler` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `extension` attribute from `crawler` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getConfig()['crawler']['filename']) || empty($this->getConfig()['crawler']['filename'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `filename` attribute from `crawler` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `filename` attribute from `crawler` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getConfig()['crawler']['timeout']) || empty($this->getConfig()['crawler']['timeout'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `timeout` attribute from `crawler` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `timeout` attribute from `crawler` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getConfig()['crawler']['try_again']) || empty($this->getConfig()['crawler']['try_again'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `try_again` attribute from `crawler` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `try_again` attribute from `crawler` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getConfig()['crawler']['try_again_delay']) || empty($this->getConfig()['crawler']['try_again_delay'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `try_again_delay` attribute from `crawler` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `try_again_delay` attribute from `crawler` configuration from `config/autoload/global.php`");
        }
        if(empty($this->getOptions())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawler_targets->target` configuration");
        }
        if(!isset($this->getOptions()['active'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `active` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `active` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['country']) || empty($this->getOptions()['country'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `country` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `country` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['pattern']) || empty($this->getOptions()['pattern'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `pattern` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `pattern` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['crawl_delay'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawl_delay` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawl_delay` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['crawl_intensity']) || empty($this->getOptions()['crawl_intensity'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawl_intensity` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawl_intensity` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['crawl_url']) || empty($this->getOptions()['crawl_url'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawl_url` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawl_url` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['crawler_class']) || empty($this->getOptions()['crawler_class'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawler_class` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawler_class` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['matcher_class']) || empty($this->getOptions()['matcher_class'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `matcher_class` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `matcher_class` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['order']) || empty($this->getOptions()['order'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `order` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `order` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['process'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `process` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `process` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['test'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `test` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `test` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['parser'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `parser` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `parser` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['parser']['listings'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `parser->listings` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `parser->listings` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['parser']['pagination'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `parser->pagination` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `parser->pagination` config for `crawler_targets->target`");
        }
        if(!isset($this->getOptions()['parser']['process'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `parser->process` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `parser->process` config for `crawler_targets->target`");
        }
        if(empty($this->getProxySettings())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `socks5_proxy` configuration from `config/autoload/proxy.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `writer` configuration");
        }
        if(!isset($this->getProxySettings()['guzzle_http_client']) || empty($this->getProxySettings()['guzzle_http_client'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `guzzle_http_client` attribute from `socks5_proxy` configuration from `config/autoload/proxy.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `guzzle_http_client` attribute from `socks5_proxy` configuration from `config/autoload/proxy.global.php`");
        }
        if(!isset($this->getProxySettings()['user_agents']) || empty($this->getProxySettings()['user_agents'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `user_agents` attribute from `socks5_proxy` configuration from `config/autoload/proxy.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `user_agents` attribute from `socks5_proxy` configuration from `config/autoload/proxy.global.php`");
        }
        if(empty($this->getTargetName())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawler_targets->target` configuration");
        }
    }
}
