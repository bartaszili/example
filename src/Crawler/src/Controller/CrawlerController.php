<?php

declare(strict_types=1);

namespace Crawler\Controller;

use Psr\Container\ContainerInterface;

class CrawlerController {
    private $class = '';
    private ContainerInterface $container;
    private $crawler_targets = [];
    private $function = '';
    private $limit = 0;
    private $offset = 0;
    private $order = 'desc';
    private $process = false;
    private $url = '';

    public function __construct(
        ContainerInterface $container,
        array $crawler_targets
    ) {
        $this->setContainer($container);
        $this->setCrawlerTargets($crawler_targets);
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getClass(): string { return $this->class; }
    public function setClass(string $input): self { $this->class = $input; return $this; }

    private function getContainer(): ContainerInterface { return $this->container; }
    private function setContainer(ContainerInterface $input): void { $this->container = $input; }

    private function getCrawlerTargets(): array { return $this->crawler_targets; }
    private function setCrawlerTargets(array $input): void { $this->crawler_targets = $input; }

    private function getFunction(): string { return $this->function; }
    public function setFunction(string $input): self { $this->function = $input; return $this; }

    private function getLimit(): int { return $this->limit; }
    public function setLimit(int $input): self { $this->limit = $input; return $this; }

    private function getOffset(): int { return $this->offset; }
    public function setOffset(int $input): self { $this->offset = $input; return $this; }

    private function getOrder(): string { return $this->order; }
    public function setOrder(string $input): self { $this->order = $input; return $this; }

    private function getProcess(): bool { return $this->process; }
    public function setProcess(bool $input): self { $this->process = $input; return $this; }

    private function getUrl(): string { return $this->url; }
    public function setUrl(string $input): self { $this->url = $input; return $this; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    /**
     * Special case handling.
     * Avoid app crash if crawler_class is not valid for target.
     * Skip and continue with next target.
     * Valid pattern: 'Example\Word\Class'
     */
    private function checkCrawlerClass(array $input): bool {
        $return = true;
        if(isset($input['crawler_class']) && preg_match("/(?:[A-Z]{1}\w+\/){2}(?:[A-Z]{1}\w+){1}/", str_replace("\\", "/", $input['crawler_class']))) {
            if(!$this->getContainer()->has($input['crawler_class'])) {
                $data = [
                    'message' => [
                        'description' => 'Unregistered class name',
                        'details' => "Unregistered class name: `{$input['crawler_class']}`"
                    ],
                    'logger' => get_called_class().'::checkCrawlerClass'
                ];
                $this->getLogger()->setData($data);
                $return = false;
            }
        } else {
            $data = [
                'message' => [
                    'description' => 'Invalid class name',
                    'details' => "Invalid class name: `{$input['crawler_class']}`"
                ],
                'logger' => get_called_class().'::checkCrawlerClass'
            ];
            $this->getLogger()->setData($data);
            $return = false;
        }
        return $return;
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function run(): void {
        if(empty($this->getCrawlerTargets())) { return; }
        $targets = [];
        if(!empty($this->getClass())) {
            foreach($this->getCrawlerTargets() as $key => $value) {
                if($this->getClass() == $value['crawler_class']) {
                    if(!empty($this->getUrl())) {
                        $value['active']          = true;
                        $value['crawl_url']       = [$this->getUrl()];
                        $value['crawl_intensity'] = '1 second';
                        $value['test']            = true;
                    }
                    $targets[$key] = $value;
                }
            }
        } else {
            $targets = $this->getCrawlerTargets();
        }
        foreach($targets as $target_name => $target_options) {
            if(!$this->checkCrawlerClass($target_options) || !$target_options['active']) { continue; }
            if(!empty($this->getLimit())) { $target_options['crawl_limit'] = $this->getLimit(); }
            if(!empty($this->getOffset())) { $target_options['crawl_offset'] = $this->getOffset(); }
            if(is_bool($this->getProcess())) { $target_options['process'] = $this->getProcess(); }
            $target = $this->getContainer()->get($target_options['crawler_class']);
            $target->setOptions($target_options);
            $target->run();
        }
    }

    public function checker(): void {
        if(empty($this->getCrawlerTargets())) { return; }
        $targets = [];
        if(!empty($this->getClass())) {
            foreach($this->getCrawlerTargets() as $key => $value) {
                if($this->getClass() == $value['crawler_class']) {
                    $targets[$key] = $value;
                }
            }
        } else {
            $targets = $this->getCrawlerTargets();
        }
        foreach($targets as $target_name => $target_options) {
            if(!$this->checkCrawlerClass($target_options) || !$target_options['active']) { continue; }
            if(!empty($this->getLimit())) { $target_options['crawl_limit'] = $this->getLimit(); }
            if(!empty($this->getOffset())) { $target_options['crawl_offset'] = $this->getOffset(); }
            if(!empty($this->getOrder())) { $target_options['order'] = $this->getOrder(); }
            $target = $this->getContainer()->get($target_options['crawler_class']);
            $target->setOptions($target_options);
            $target->check();
        }
    }

    public function updater(): void {
        if(empty($this->getCrawlerTargets())) { return; }
        $targets = [];
        if(!empty($this->getClass())) {
            foreach($this->getCrawlerTargets() as $key => $value) {
                if($this->getClass() == $value['crawler_class']) {
                    $targets[$key] = $value;
                }
            }
        } else {
            $targets = $this->getCrawlerTargets();
        }
        foreach($targets as $target_name => $target_options) {
            if(!$this->checkCrawlerClass($target_options) || !$target_options['active']) { continue; }
            if(!empty($this->getLimit())) { $target_options['crawl_limit'] = $this->getLimit(); }
            if(!empty($this->getOffset())) { $target_options['crawl_offset'] = $this->getOffset(); }
            if(!empty($this->getFunction())) { $target_options['function'] = $this->getFunction(); }
            if(is_bool($this->getProcess())) { $target_options['process'] = $this->getProcess(); }
            $target = $this->getContainer()->get($target_options['crawler_class']);
            $target->setOptions($target_options);
            $target->update();
        }
    }
}
