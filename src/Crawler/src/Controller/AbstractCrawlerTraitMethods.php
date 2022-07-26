<?php

declare(strict_types=1);

namespace Crawler\Controller;

use App\Handler\AbstractAppTraitMethods;
use Commands\Command\AbstractCommandsTraitMethods;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Logger\Controller\LoggerController;
use Psr\Http\Message\UriInterface;
use Writer\Adapter\FilesystemAdapter;
use Writer\Controller\WriterController;
use \DateTime;
use \Exception;
use \Throwable;

trait AbstractCrawlerTraitMethods {
    use AbstractAppTraitMethods;
    use AbstractCommandsTraitMethods;

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        $this->client();
        $this->logger($this->getTargetName());
        $this->writer($this->getTargetName());
        if(isset($this->getOptions()['try_again']) && !empty($this->getOptions()['try_again'])) {
            $this->try_again = $this->getOptions()['try_again'];
        } else {
            $this->try_again = $this->getConfig()['crawler']['try_again'];
        }
        if(isset($this->getOptions()['try_again_delay']) && !empty($this->getOptions()['try_again_delay'])) {
            $this->try_again_delay = $this->getOptions()['try_again_delay'];
        } else {
            $this->try_again_delay = $this->getConfig()['crawler']['try_again_delay'];
        }
    }

    private function allowedFrom(string $date, string $intensity): bool {
        $limit = date('Y-m-d H:i:s', strtotime($date.' +'.$intensity));
        $now = date('Y-m-d H:i:s', strtotime('now'));
        if($limit <= $now) { return true; }
        return false;
    }

    private function checker_status_eval(?array $input): bool {
        $return = false;
        $context = stream_context_create( [
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
                'max_redirects' => 1,
                'user_agent' => $this->randomUserAgent($this->getProxySettings()['user_agents']),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        if(isset($input) && !empty($input)) {
            $status_code = null;
            if(isset($input[0]) && !empty($input[0])) {
                preg_match("/^\w*\/?.*\s*(\d{3})\s*\w*/", $input[0], $match);
                if(!empty($match) && isset($match[1])) {
                    $status_code = $match[1];
                }
            }
            if(is_null($status_code)) { return $return; }

            $location_url = null;
            if (isset($input['Location']) && !empty($input['Location']))
            {
                $location_url = $input['Location'];
                if (is_array($location_url) && ! empty($location_url[0]))
                {
                    $location_url = $location_url[0];
                }
            }

            if($status_code == 200) {
                if($this->getTargetName() == 'topreality') {
                    $html = null;
                    $html = file_get_contents($this->getUri()->__toString(), false, $context);
                    if(!empty($html)) {
                        if(!preg_match("/(?:inzer\w+\s+neexist\w+)/uim", $html)) {
                            $return = true;
                        }
                    }
                } else {
                    $return = true;
                }
            }
            if($status_code >= 300 && $status_code < 400) {
                if(is_null($location_url)) { return $return; }
                $id_local = [];
                $id_local_short = [];
                $id_remote = [];
                preg_match($this->getOptions()['pattern'], $this->getUri()->__toString(), $id_local);
                preg_match($this->getOptions()['pattern_short'], $this->getUri()->__toString(), $id_local_short);
                preg_match($this->getOptions()['pattern'], $location_url, $id_remote);
                if(isset($id_remote[1]) && !empty($id_remote[1])) {
                    if(isset($id_local[1]) && !empty($id_local[1])) {
                        if($id_local[1] == $id_remote[1]) {
                            $return = true;
                        }
                    } elseif(isset($id_local_short[1]) && !empty($id_local_short[1])) {
                        if($id_local_short[1] == $id_remote[1]) {
                            $return = true;
                        }
                    }
                }
            }
        }
        return $return;
    }

    private function client(): void {
        // disable proxy
        if($this->getTargetName() == 'bazos') {
            $options = $this->getProxySettings()['guzzle_http_client'];
            unset($options['proxy']);
            unset($options['curl']);
            $this->setProxySettings(['guzzle_http_client' => $options]);
        }
        $this->setClient(new Client($this->getProxySettings()['guzzle_http_client']));
    }

    private function crawl(bool $isRetiredCheck = true, bool $checkerMode = false): ?string {
        if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['crawled']) {
            $data = [
                'message' => ['debug init'],
                'url' => $this->getUri()->__toString(),
                'logger' => get_called_class().'::crawl'
            ];
            $this->getLogger()->setData($data);
        }

        if($checkerMode == true) {
            $context = stream_context_create( [
                'http' => [
                    'method' => 'HEAD',
                    'ignore_errors' => true,
                    'max_redirects' => 1,
                    'user_agent' => $this->randomUserAgent($this->getProxySettings()['user_agents']),
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $short = $this->shortFilename($this->getUri());
            if($this->getLogger()->getDebug()['console_log']['crawler'] == true && !empty($short)) {
                $short = str_replace("start/", "", $short);
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s.u')." Checker is processing: ".$short.PHP_EOL);
            }

            for($this->try_again_count; $this->try_again_count <= $this->try_again && $this->try_again_loop === true; $this->try_again_count++) {
                if($this->try_again_count <= $this->try_again) {
                    $headers = false;
                    if($this->getTargetName() != 'nehnutelnosti' && $this->getTargetName() != 'reality')
                    {
                        $headers = get_headers($this->getUri()->__toString(), 1, $context);
                    }
                    else
                    {
                        // disable redirects
                        if ($this->getTargetName() == 'nehnutelnosti')
                        {
                            $options = $this->getProxySettings()['guzzle_http_client'];
                            $options[RequestOptions::ALLOW_REDIRECTS] = false;
                            $options[RequestOptions::HEADERS]['User-Agent'] = $this->randomUserAgent($this->getProxySettings()['user_agents']);
                            $this->setClient(new Client($options));
                        }
                        $request = false;
                        $request = $this->getClient()->request('GET', $this->getUri());
                        $headers = $request->getHeaders();
                        if(is_countable($headers) && count($headers)) {
                            $headers[0] = (string) $request->getStatusCode();
                        }
                    }
                    if($headers === false) {
                        $delay = (empty($this->try_again_delay)) ? 0 : $this->try_again_delay / 1000;
                        if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                    } else {
                        break;
                    }
                }
            }

            if(!isset($headers) || $headers === false) {
                $data = [
                    'message' => ['Check failed'],
                    'url' => $this->getUri()->__toString(),
                    'logger' => get_called_class().'::crawl'
                ];
                $this->getLogger()->setData($data);
                return '';
            }

            if(isset($headers) && !empty($headers)) {
                if($this->checker_status_eval($headers) === true) { return ''; }
            }

            return null;
        }

        if($isRetiredCheck) {
            if($this->isRetired($this->getUri())) {
                if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['should_crawl']) {
                    $data = [
                        'message' => ['debug false retired'],
                        'url' => $this->getUri()->__toString(),
                        'logger' => get_called_class().'::crawl'
                    ];
                    $this->getLogger()->setData($data);
                }
                return null;
            }
        }

        $request =  false;
        $response = false;
        $i = 0;
        reinit:
        try {
            $short = $this->shortFilename($this->getUri());
            if($this->getLogger()->getDebug()['console_log']['crawler'] == true && !empty($short)) {
                $short = str_replace("start/", "", $short);
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s.u')." Crawler is processing: ".$short.PHP_EOL);
            }
            $request = $this->getClient()->request('GET', $this->getUri());
        } catch (Throwable $e) {
            if($i < $this->try_again) {
                $delay = (empty($this->try_again_delay)) ? 0 : $this->try_again_delay / 1000;
                if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                $i++;
                goto reinit;
            }

            $data = [
                'message' => [
                    'description' => 'Crawl failed',
                    'details' => $e
                ],
                'url' => $this->getUri()->__toString(),
                'logger' => get_called_class().'::crawl'
            ];
            $this->getLogger()->setData($data);
            return null;
        }

        if($request->getStatusCode() !== 200) {
            $data = [
                'message' => [
                    'description' => 'Crawl failed status',
                    'details' => $request->getStatusCode().' '.$request->getReasonPhrase()
                ],
                'url' => $this->getUri()->__toString(),
                'logger' => get_called_class().'::crawl'
            ];
            $this->getLogger()->setData($data);
            return null;
        }

        $response = $request->getBody()->getContents();
        if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['crawled']) {
            $data = [
                'message' => [
                    'description' => 'debug response',
                    'details' => $request->getStatusCode().' '.$request->getReasonPhrase()
                ],
                'url' => $this->getUri()->__toString(),
                'logger' => get_called_class().'::crawl'
            ];
            $this->getLogger()->setData($data);
        }
        return $response;
    }

    private function crawl_process(): void {
        if($this->getExit() || $this->getTargetName() == 'bazos') { return; }
        $search = glob($this->getFilename());
        $this->setFilename('');
        if(!empty($search)) {
            $targets = explode(PHP_EOL, file_get_contents($search[0]));
            if(file_exists($search[0])) { unlink($search[0]); }
            $i = 0;
            foreach($targets as $url) {
                $this->setUri($this->uri($url));
                $response = $this->crawl();
                if(!is_null($response)) { $this->save($response); }
                $delay = (empty($this->getOptions()['crawl_delay'])) ? 0 : $this->getOptions()['crawl_delay'] / 1000;
                if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                $i++;
            }
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s').' '.$this->getSessionId().' # Crawler counter  # '.$i.PHP_EOL);
        }
    }

    private function filenameDate(string $filename, bool $extension = true): string {
        $end = ($extension) ? "\.".$this->getWriter()->getOptions()['extension']."$" : "";
        $pattern = "/([\d]{4})([\d]{2})([\d]{2})_([\d]{2})([\d]{2})([\d]{2})".$end."/";
        preg_match($pattern, $filename, $match);
        $mktime = mktime(
            (int) $match[4],
            (int) $match[5],
            (int) $match[6],
            (int) $match[2],
            (int) $match[3],
            (int) $match[1]
        );
        return date('Y-m-d H:i:s', $mktime);
    }

    private function isFileRetired(string $input): bool {
        if(empty($input)) { return false; }
        $date = $this->filenameDate($input, false);
        if(empty($date)) { return false; }
        if($this->allowedFrom($date, $this->getConfig()['crawler']['timeout'])) { return true; }
        return false;
    }

    private function isRetired(UriInterface $uri): bool {
        $filename = $this->getWriter()->getStorage()->sanitize($this->shortFilename($uri));
        $dates = [];
        foreach(glob($this->getWriter()->getStorage()->getPath().$filename.'*'.$this->getWriter()->getOptions()['extension']) as $file) {
            $fileDate = $this->filenameDate($file);
            $dates[$fileDate] = $fileDate;
        }
        krsort($dates);
        if(empty($dates)) { return false; }
        if(!$this->allowedFrom(reset($dates), $this->getOptions()['crawl_intensity'])) { return true; }
        return false;
    }

    private function logger(string $input): LoggerController {
        $logger = $this->getLogger();
        $logger->setFilename($input.'.'.$this->getConfig()['logger']['extension']);
        return $logger;
    }

    private function parse_process(): void {
        if($this->getExit()) { return; }
        if($this->getLogger()->getDebug()['scanner'] && $this->getLogger()->getDebug()['short_filename']) {
            $this->getScanner()->run($this->getOptions()['crawler_class']);
        }
    }

    private function parserCheck(?array $array, ?string $file): void {
        if(empty($array)) {
            $data = [
                'message' => ['Empty response from `Parser`'],
                'url' => $file,
                'logger' => get_called_class().'::parserCheck'
            ];
            $this->getLogger()->setData($data);
            $this->setExit(true);
        }
    }

    private function randomUserAgent(?array $input): ?string {
        if(empty($input)) { return null; }
        return (string) $input[array_rand($input)];
    }

    private function save(string $content, string $filename = ''): void {
        if(empty($content)) { return; }
        $filename = (empty($filename)) ? $this->shortFilename($this->getUri()) : $filename;
        $this->getWriter()->writeRawData($content, $filename);
    }

    private function saveList(array $input, string $filename = ''): void {
        if(!empty($filename)) { $filename .= '_'; }
        $file = $this->getWriter()->getStorage()->getPath().$this->getSessionId().$filename.$this->getConfig()['crawler']['filename'].'.'.$this->getConfig()['crawler']['extension'];
        if(!is_dir(pathinfo($file)['dirname'])) { mkdir(pathinfo($file)['dirname'], octdec($this->getLogger()->getPermissions()['directory']), true); }
        chmod(pathinfo($file)['dirname'], octdec($this->getLogger()->getPermissions()['directory']));
        $this->setFilename($file);
        if(empty($input)) { return; }
        $input = implode(PHP_EOL,$input);
        file_put_contents($file, $input, LOCK_EX);
        chmod($file, octdec($this->getLogger()->getPermissions()['file']));
    }

    private function shortFilename(UriInterface $input): ?string {
        if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['short_filename']) {
            $data = [
                'message' => ['debug init'],
                'url' => $input->__toString(),
                'logger' => get_called_class().'::shortFilename'
            ];
            $this->getLogger()->setData($data);
        }
        if(!$this->getLogger()->getDebug()['short_filename']) { return $input->__toString(); }
        if($this->getOptions()['test']) {
            preg_match($this->getOptions()['pattern_short'], $input->getPath(), $match);
        } else {
            preg_match($this->getOptions()['pattern'], $input->getPath(), $match);
        }

        if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['short_filename']) {
            $data = [
                'message' => $match,
                'url' => $input->__toString(),
                'logger' => get_called_class().'::shortFilename'
            ];
            $this->getLogger()->setData($data);
        }
        if(!empty($match) && isset($match[1])) {
            if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['short_filename']) {
                $data = [
                    'message' => ['debug true'],
                    'url' => $input->__toString(),
                    'logger' => get_called_class().'::shortFilename'
                ];
                $this->getLogger()->setData($data);
            }
            return (string) $this->getWriter()->getOptions()['filename_prefix'].DIRECTORY_SEPARATOR.$input->getHost().DIRECTORY_SEPARATOR.$this->getWriter()->getOptions()['id_prefix'].$match[1];
        }
        return null;
    }

    /** Auto insert, add or update options property */
    private function push(array $input): void {
        $options = $this->getOptions();
        if(empty($options)) {
            $this->setOptions($input);
        } else {
            foreach($input as $key => $value) {
                $options[$key] = $value;
            }
            $this->setOptions($options);
        }
    }

    private function uri(string $input): UriInterface {
        $return = new Uri($input);
        return $return;
    }

    private function writer(string $input): WriterController {
        $writer = $this->getWriter();
        $storage = null;
        if(!empty($this->getConfig()['writer'])) {
            $storage = new FilesystemAdapter($this->getConfig()['writer']['storage_path'].$input.DIRECTORY_SEPARATOR);
        }
        if($storage) {
            $writer->setStorage($storage);
        }
        return $writer;
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function run(): void {
        if($this->getOptions()['test']) {
            // delete all
            foreach(glob($this->getWriter()->getStorage()->getPath().'*') as $file) {
                if(file_exists($file)) { unlink($file); }
            }
            // set target url
            $this->setUri($this->uri($this->getOptions()['crawl_url'][0]));
            // crawl
            $response = $this->crawl();
            // save
            if(!is_null($response)) { $this->save($response); }
            // process
            $this->getScanner()->run($this->getOptions()['crawler_class']);
            // exit
            return;
        }
        // cleanup
        foreach(glob($this->getWriter()->getStorage()->getPath().'*'.$this->getConfig()['crawler']['filename'].'.'.$this->getConfig()['crawler']['extension']) as $file) {
            if(file_exists($file) && $this->isFileRetired($file)) { unlink($file); }
        }
        foreach(glob($this->getWriter()->getStorage()->getPath().'*check*') as $file) {
            if(file_exists($file) && $this->isFileRetired($file)) { unlink($file); }
        }
        foreach(glob($this->getWriter()->getStorage()->getPath().'*listings*') as $file) {
            if(file_exists($file) && $this->isFileRetired($file)) { unlink($file); }
        }
        foreach(glob($this->getWriter()->getStorage()->getPath().'*pagination*') as $file) {
            if(file_exists($file) && $this->isFileRetired($file)) { unlink($file); }
        }

        foreach($this->getOptions()['crawl_url'] as $url) {
            $time = new DateTime('now');
            $this->setSessionId(uniqid().'_'.$time->format('Ymd_His').'_');
            print('  '.$time->format('Y-m-d H:i:s').' '.$this->getSessionId().' '.$url.PHP_EOL);
            $this->setExit(false);
            $this->setUri($this->uri($url));

            $this->steps();

            $this->crawl_process();
            foreach(glob($this->getWriter()->getStorage()->getPath().$this->getSessionId().'*') as $file) {
                unlink($file);
            }
            if($this->getOptions()['process'] === false) { $this->setExit(true); }
            $this->parse_process();
        }
    }

    /**
     * No need to verify already disabled items.
     * Normal day-to-day crawling will auto enable disabled items when they re-appear.
     */
    public function check(): void {
        $time = new DateTime('now');
        $this->setSessionId(uniqid().'_'.$time->format('Ymd_His').'_');

        $base_url = parse_url($this->getOptions()['crawl_url'][0], PHP_URL_HOST);
        $array = $this->getScanner()->fetch($base_url, $this->getOptions()['crawl_limit'], $this->getOptions()['crawl_offset'], $this->getOptions()['order']);

        $rsp = [];
        $active = [];
        foreach($array as $item) {
            if(isset($item['is_active']) || !empty($item['is_active'])) {
                $active[] = $item;

                $pattern = "/^http(?:s?)/";
                if(!preg_match($pattern, $item['origin_url'])) {
                    $item['origin_url'] = 'https://'.$item['origin_url'];
                }

                $this->setUri($this->uri($item['origin_url']));
                $response = $this->crawl(false, true);
                if(is_null($response)) {
                    $rsp[] = $item['id'];
                }
                $delay = (empty($this->getOptions()['crawl_delay'])) ? 0 : $this->getOptions()['crawl_delay'] / 1000;
                if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
            }
        }

        foreach($active as $item) {
            if(array_search($item['id'], $rsp) !== false) {
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s.u').' Disabling: '.$item['origin_url'].PHP_EOL);
                $this->getScanner()->updateIsActive($item['id'], false);
            }
        }
    }

    public function update(): void {
        $time = new DateTime('now');
        $this->setSessionId(uniqid().'_'.$time->format('Ymd_His').'_');

        $base_url = parse_url($this->getOptions()['crawl_url'][0], PHP_URL_HOST);
        if(!isset($this->getOptions()['function'])) { $this->push(['function' => '']); }
        if(isset($this->getOptions()['function']) && $this->getOptions()['function'] == 'origin_url') { $this->push(['test' => true]); }

        $this->getScanner()->setFunction($this->getOptions()['function'])
                           ->setUpdater(true);

        $list = $this->getScanner()->list($base_url, $this->getOptions()['crawl_limit'], $this->getOptions()['crawl_offset']);

        $i = 0;
        foreach($list as $item) {
            $pattern = "/^http(?:s?)/";
            if(!preg_match($pattern, $item['origin_url'])) {
                $item['origin_url'] = 'https://'.$item['origin_url'];
            }
            $this->setUri($this->uri($item['origin_url']));

            $response = $this->crawl();
            if(!is_null($response)) { $this->save($response); }
            else {
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s.u').' Disabling: '.$item['origin_url'].PHP_EOL);
                $this->getScanner()->updateIsActive($item['id'], false);
            }
            $delay = (empty($this->getOptions()['crawl_delay'])) ? 0 : $this->getOptions()['crawl_delay'] / 1000;
            if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
            $i++;
        }
        $time = new DateTime('now');
        print('  '.$time->format('Y-m-d H:i:s').' '.$this->getSessionId().' # Crawler counter  # '.$i.PHP_EOL);

        if($this->getOptions()['process'] === false) { $this->setExit(true); }
        $this->parse_process();
    }
}
