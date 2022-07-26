<?php

declare(strict_types=1);

namespace TargetReality\Controller;

use Crawler\Controller\AbstractCrawlerTraitClass;

class CrawlerRealityController {
    use AbstractCrawlerTraitClass;

    public function __construct(
        $config,
        $logger,
        array $options,
        $parser,
        array $proxy_settings,
        $scanner,
        string $target_name,
        $writer
    ) {
        $this->setConfig($config);
        $this->setLogger($logger);
        $this->setOptions($options);
        $this->setParser($parser);
        $this->setProxySettings($proxy_settings);
        $this->setScanner($scanner);
        $this->setTargetName($target_name);
        $this->setWriter($writer);
        $this->configCheck();
        $this->_init();
    }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function crawl_listings(): void {
        if($this->getExit()) { return; }
        // init props
        (int) $count     = 0;
        (int) $i         = 0;
        (int) $limit     = 0;
        (int) $offset    = 0;
        (array) $targets = [];

        $search = glob($this->getFilename());
        if(!empty($search)) {
            $targets = explode(PHP_EOL, file_get_contents($search[0]));
            if(file_exists($search[0])) { unlink($search[0]); }
        }

        if(!empty($targets)) {
            $this->setUri($this->uri($targets[0]));
            $response = $this->crawl(false);
            $this->setFilename($this->getSessionId().'check_'.$this->getTargetName());
            if(!is_null($response)) { $this->save($response, $this->getFilename()); }
            $check = glob($this->getWriter()->getStorage()->getPath().$this->getFilename().'*');
            if(!empty($check)) {
                $parser = $this->getParser()
                                ->setTarget($check[0])
                                ->setParsingMap($this->getOptions()['parser']['listings'])
                                ->get();
                if(file_exists($check[0])) { unlink($check[0]); }

                $this->parserCheck($parser, $check[0]);
                if($this->getExit()) { return; }

                (int) $list  = count($parser);
                (int) $count = count($targets) * $list;
                if($this->getOptions()['crawl_limit'] > 0) {
                    $limit = ceil($this->getOptions()['crawl_limit'] / $list);
                    settype($limit, 'int');
                }
                if($this->getOptions()['crawl_offset'] > 0) {
                    if($this->getOptions()['crawl_offset'] >= $count) { $this->setExit(true); }
                    $offset = floor($this->getOptions()['crawl_offset'] / $list);
                    settype($offset, 'int');
                    $this->setShift($this->getOptions()['crawl_offset'] - ($offset * $list));
                    $targets = array_slice($targets, $offset);
                }
            }

            foreach($targets as $url) {
                $this->setUri($this->uri($url));
                preg_match($this->getOptions()['parser']['pattern'], $this->getUri()->__toString(), $match);
                $response = $this->crawl(false);
                $this->setFilename($this->getSessionId().'listings-'.$this->leadingZeros((int) $match[2],6).'_'.$this->getTargetName());
                if(!is_null($response)) { $this->save($response, $this->getFilename()); }
                $delay = (empty($this->getOptions()['crawl_delay'])) ? 0 : $this->getOptions()['crawl_delay'] / 1000;
                if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                if(!empty($this->getOptions()['crawl_limit']) && $i == $limit) { break; }
                $i++;
            }
        }
        $this->setFilename('');
    }

    private function crawl_pagination(): void {
        if($this->getExit()) { return; }
        $response = $this->crawl(false);
        $this->setFilename($this->getSessionId().'pagination_'.$this->getTargetName());
        if(!is_null($response)) { $this->save($response, $this->getFilename()); }
    }

    private function parse_listings(): void {
        if($this->getExit()) { return; }
        $search = glob($this->getWriter()->getStorage()->getPath().$this->getSessionId().'listings-*_'.$this->getTargetName().'*');
        $list = [];
        $i = 0;
        if(!empty($search)) {
            foreach($search as $item) {
                $parser = $this->getParser()
                               ->setTarget($item)
                               ->setParsingMap($this->getOptions()['parser']['listings'])
                               ->get();
                if(!empty($parser)) {
                    if($this->getOptions()['crawl_offset'] > 0 && $this->getShift() > 0 && $i == 0) {
                        $parser = array_slice($parser, $this->getShift());
                    }
                    $list = array_merge($list, $parser);
                }
                unlink($item);
                if(!empty($this->getOptions()['crawl_limit']) && count($list) >= $this->getOptions()['crawl_limit']) { break; }
                $i++;
            }
    
            if(!empty($this->getOptions()['crawl_limit']) && count($list) > $this->getOptions()['crawl_limit']) {
                $list = array_slice($list, 0, $this->getOptions()['crawl_limit']);
            }

            foreach($list as $key => $value) {
                $list[$key] = $this->getUri()->getScheme().'://'.$this->getUri()->getHost().$value;
            }

            // cleanup
            foreach($search as $item) {
                if(file_exists($item)) { unlink($item); }
            }

            $this->saveList($list, 'crawl');
        }
    }

    private function parse_pagination(): void {
        if($this->getExit()) { return; }
        $search = glob($this->getWriter()->getStorage()->getPath().$this->getFilename().'*'.$this->getWriter()->getOptions()['extension']);
        $this->setFilename('');
        if(!empty($search)) {
            $parser = $this->getParser()
                           ->setTarget($search[0])
                           ->setParsingMap($this->getOptions()['parser']['pagination'])
                           ->get();
            if(file_exists($search[0])) { unlink($search[0]); }

            $this->parserCheck($parser, $search[0]);
            if($this->getExit()) { return; }

            if(empty($parser['count'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `count` attribute from parsed data"
                    ],
                    'url' => $search[0],
                    'logger' => get_called_class().'::parse_pagination'
                ];
                $this->getLogger()->setData($data);
                $this->setExit(true);
            }
            if(empty($parser['list'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `list` attribute from parsed data"
                    ],
                    'url' => $search[0],
                    'logger' => get_called_class().'::parse_pagination'
                ];
                $this->getLogger()->setData($data);
                $this->setExit(true);
            }
            if($this->getExit()) { return; }

            $match = [];
            if(!empty($parser['next'])) {
                preg_match($this->getOptions()['parser']['pattern'], $parser['next'], $match);
                // check match
                if(empty($match)) {
                    $data = [
                        'message' => [
                            'description' => 'Missing URL path',
                            'details' => "Missing `pagination` URL path",
                            $parser['next']
                        ],
                        'logger' => get_called_class().'::parse_pagination'
                    ];
                    $this->getLogger()->setData($data);
                    $this->setExit(true);
                }
                if($this->getExit()) { return; }
            }

            $pattern = "/^(\d+)/";
            preg_match($pattern, $parser['count'], $count);

            // check count
            if(empty($count)) {
                $data = [
                    'message' => [
                        'description' => 'Missing count',
                        'details' => "Missing `count` total number of all adverts",
                        $parser['count']
                    ],
                    'logger' => get_called_class().'::parse_pagination'
                ];
                $this->getLogger()->setData($data);
                $this->setExit(true);
            }
            if($this->getExit()) { return; }

            (int) $last = ceil($count[1] / $parser['list']);

            $pagination = [];
            if(!empty($match)) {
                for($i = 1; $i <= $last; $i++) {
                    $pagination[] = $this->getUri()->getScheme().'://'.$this->getUri()->getHost().$match[1].$i;
                }
            } else {
                $pagination[] = $this->getUri()->__toString();
            }

            if(!empty($pagination)) { $this->saveList($pagination, 'pagination'); }
        }
    }

    private function steps(): void {
        $this->crawl_pagination();
        $this->parse_pagination();
        $this->crawl_listings();
        $this->parse_listings();
    }
}
