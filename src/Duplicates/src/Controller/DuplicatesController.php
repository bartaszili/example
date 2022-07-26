<?php

declare(strict_types=1);

namespace Duplicates\Controller;

use Api\Entity\Property;
use App\Handler\AbstractAppTraitMethods;
use Commands\Command\AbstractCommandsTraitMethods;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Logger\Controller\LoggerController;
use Services\Helper\FuzzyHelper;
use Services\Helper\SlugifyHelper;
use Services\Helper\StopWordsHelper;
use \DateTime;
use \Exception;
use \GlobIterator;

class DuplicatesController {
    use AbstractAppTraitMethods;
    use AbstractCommandsTraitMethods;

    private $country = 'sk';
    private EntityManager $entity_manager;
    private FuzzyHelper $fuzz;
    private LoggerController $logger;
    private $options = [];
    private Property $property;
    private $session = '';
    private $session_id = '';
    private SlugifyHelper $slug;
    private StopWordsHelper $stop;
    private $writer = [];

    private $test = false; // default: false

    public function __construct(
        $duplicates,
        EntityManager $entity_manager,
        FuzzyHelper $fuzz,
        LoggerController $logger,
        SlugifyHelper $slug,
        StopWordsHelper $stop,
        array $writer
    ) {
        $this->setOptions($duplicates);
        $this->setEntityManager($entity_manager);
        $this->setFuzz($fuzz);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->setStop($stop);
        $this->setWriter($writer);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getCountry(): string { return $this->country; }
    private function setCountry(string $input): void { $this->country = $input; }

    private function getEntityManager(): EntityManager { return $this->entity_manager; }
    private function setEntityManager(EntityManager $input): void { $this->entity_manager = $input; }

    private function getFuzz(): FuzzyHelper { return $this->fuzz; }
    private function setFuzz(FuzzyHelper $input): void { $this->fuzz = $input; }

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    private function getOptions(): array { return $this->options; }
    private function setOptions(array $input): void { $this->options = $input; }

    private function getSession(): string { return $this->session; }
    private function setSession(string $input): void { $this->session = $input; }

    private function getSessionId(): string { return $this->session_id; }
    private function setSessionId(string $input): void { $this->session_id = $input; }

    private function getSlug(): SlugifyHelper { return $this->slug; }
    private function setSlug(SlugifyHelper $input): void { $this->slug = $input; }

    private function getStop(): StopWordsHelper { return $this->stop; }
    private function setStop(StopWordsHelper $input): void { $this->stop = $input; }

    private function getWriter(): array { return $this->writer; }
    private function setWriter(array $input): void { $this->writer = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        if($this->test === true) {
            $this->setSessionId('testsessionid');
            $this->setSession('testsessionid_20210101_010101_');
            return;
        }
        $this->setSessionId(uniqid());
        $time = new DateTime('now');
        $this->setSession($this->getSessionId().'_'.$time->format('Ymd_His').'_');
    }

    private function cleanup(string $input): void {
        if($this->test === true) { return; }
        $iterator = new GlobIterator($input.'*');
        foreach($iterator as $item) {
            $file = $item->getFilename();
            if(strstr($file, $this->getSession()) === false) {
                $time = $this->filenameDate($file, "_", "_");
                $clean = $this->isTimeout($time, $this->getOptions()['timeout']);
                if($clean == true) {
                    if(file_exists($item->__toString())) { unlink($item->__toString()); }
                }
            }
        }
    }

    /**
     * This checks configuration options.
     * To be passed without Exception.
     *
     * @throws Exception With detailed problem description.
     */
    private function configCheck() {
        if(empty($this->getOptions())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `duplicates` configuration");
        }
        if(!isset($this->getOptions()['delay']) || empty($this->getOptions()['delay'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `delay` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `delay` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['description_length']) || empty($this->getOptions()['description_length'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `description_length` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `description_length` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['description_score']) || empty($this->getOptions()['description_score'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `description_score` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `description_score` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['description_short_length']) || empty($this->getOptions()['description_short_length'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `description_short_length` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `description_short_length` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['limit']) || empty($this->getOptions()['limit'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `limit` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `limit` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['name_length']) || empty($this->getOptions()['name_length'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `name_length` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `name_length` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['name_score']) || empty($this->getOptions()['name_score'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `name_score` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `name_score` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['timeout']) || empty($this->getOptions()['timeout'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `timeout` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `timeout` attribute from `duplicates` configuration");
        }
        if(!isset($this->getOptions()['transaction_size']) || empty($this->getOptions()['transaction_size'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `transaction_size` attribute from `duplicates` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `transaction_size` attribute from `duplicates` configuration");
        }
    }

    private function getProperties(int $limit = 0, ?int $offset = 0, bool $count = false): ?array {
        $repository = $this->getEntityManager()->getRepository(Property::class);
        $qb = $repository->createQueryBuilder('p');
        if($count === true) {
            $qb->select(
                $qb->expr()->count('p.id').' AS count'
            );
        } else {
            $qb->select([
                'p.id',
                'p.origin_host',
                'p.name',
                $qb->expr()->substring('p.description', 1, $this->getOptions()['description_length']).' AS description',
                'p.category',
                'p.type',
                'p.town',
                'p.price',
                'p.price_type',
                'p.duplicates',
                'ifnull(p.modified, p.created) AS date',
            ]);
        }
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('p.is_active', $qb->expr()->literal('1')),
                $qb->expr()->eq('p.country', $qb->expr()->literal('SK')),
                $qb->expr()->isNotNull('p.name'),
                $qb->expr()->isNotNull('p.category'),
                $qb->expr()->isNotNull('p.type'),
                $qb->expr()->eq('p.is_main', $qb->expr()->literal('1')),
            )
        )->orderBy('p.name', 'ASC')->addOrderBy('p.origin_host', 'ASC');
        if($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if($offset > 0) {
            $qb->setFirstResult($offset);
        };
        $return = $qb->getQuery()->getResult();
        return $return;
    }

    private function pushDuplicates(string $string, array $array): array {
        $return = [];
        if(empty($string)) { return $return; }
        if(empty($array)) {
            $return[] = $string;
        } else {
            $return = $array;
            $search = false;
            foreach($return as $key => $value) {
                if($string == $value) {
                    $search = true;
                    break;
                }
            }
            if($search === false) {
                $return[] = $string;
            }
        }
        return $return;
    }

    private function step1_load_data(string $input): void {
        $count = $this->getProperties(0, 0, true);
        if(!empty($count) && isset($count[0]) && isset($count[0]['count'])) {
            $count = (int) $count[0]['count'];
            if($count > 0) {
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Properties count: ".$count.PHP_EOL);
                $i = 1;
                $j = (int) ceil($count / $this->getOptions()['limit']);
                for($i; $i <= $j; $i++) {
                    $limit = $this->getOptions()['limit'];
                    $offset = ($limit * $i) - $limit;
                    $data = $this->getProperties($limit, $offset);
                    if(!empty($data)) {
                        $filename = $input.$this->getSession().'data-'.$this->leadingZeros($i,6).'.json';
                        $this->save($data, $filename);

                        $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                        if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                    }
                }
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Data saved".PHP_EOL);

                $search = glob($input.$this->getSession().'data-*.json');
                if(!empty($search)) {
                    $count = count($search);
                    $i = 0;
                    for($i; $i < ($count - 1); $i++) {
                        $data1 = $this->toArray($search[$i]);
                        $data2 = $this->toArray($search[$i + 1]);
                        if(!empty($data1) && !empty($data1)) {
                            $ids1 = array_column($data1, 'id');
                            $ids2 = array_column($data2, 'id');
                            if(!empty($ids1) && !empty($ids2)) {
                                $match = array_intersect($ids1, $ids2);
                                if(!empty($match)) {
                                    foreach($match as $item) {
                                        foreach($data2 as $key => $value) {
                                            if($item == $value['id']) {
                                                unset($data2[$key]);
                                                continue 2;
                                            }
                                        }
                                    }
                                    $this->save($data2, $search[$i + 1]);

                                    $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                                    if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                                }
                            }
                        }
                    }
                    $time = new DateTime('now');
                    print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Filtered saved data".PHP_EOL);
                }
            }
        }
    }

    private function step2_main_filter_sort(string $input): void {
        $pattern = "/(?:\&quot\;|\'|\"|\&\#39\;)/iu";
        $search = glob($input.$this->getSession().'data-*.json');
        if(!empty($search)) {
            foreach($search as $file) {
                $data = $this->toArray($file);
                $arr = [];
                foreach($data as $item) {
                    $md5 = null;
                    if(
                        array_key_exists('category', $item)
                        && array_key_exists('type', $item)
                        && array_key_exists('town', $item)
                        && array_key_exists('price', $item)
                        && array_key_exists('price_type', $item)
                    ) {
                        $md5 = md5(serialize([
                            'category' => $item['category'],
                            'type' => $item['type'],
                            'town' => $item['town'],
                            'price' => $item['price'],
                            'price_type' => $item['price_type'],
                        ]));
                        if(!empty($md5)) {
                            $arr[$md5][] = [
                                'id' => $item['id'],
                                'origin_host' => $item['origin_host'],
                                'name' => preg_replace($pattern, '', $item['name']),
                                'description' => preg_replace($pattern, '', $item['description']),
                                'duplicates' => $item['duplicates'],
                                'date' => $item['date'],
                                'is_main' => true,
                                'processed' => false,
                                'to_update' => false,
                            ];
                        }
                    }
                }
                if(!empty($arr)) {
                    $this->save($arr, $file);
                } else {
                    if(file_exists($file) && $this->test === false) { unlink($file); }
                }

                $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
            }
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Data grouped by hash #1:".PHP_EOL);

            $search = glob($input.$this->getSession().'data-*.json');
            if(!empty($search)) {
                foreach($search as $file) {
                    $data = $this->toArray($file);
                    foreach($data as $key => $value) {
                        $filename = $input.$this->getSession().'main-'.$key.'.json';
                        $grp_search = glob($filename);
                        if(!empty($grp_search) && isset($grp_search[0])) {
                            $dt = $this->toArray($filename);
                            $dt = array_merge($dt, $value);
                            if(!empty($dt)) { $this->save($dt, $filename); }
                        } else {
                            if(!empty($value)) { $this->save($value, $filename); }
                        }

                        // TODO check if impacts system performance during run
                        // $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                        // if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                    }
                    if(file_exists($file) && $this->test === false) { unlink($file); }
                }
                $print_count = count(glob($input.$this->getSession().'main-*.json'));
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Saved grouped files count:".$print_count.PHP_EOL);

                $search = glob($input.$this->getSession().'main-*.json');
                if(!empty($search)) {
                    foreach($search as $file) {
                        $data = $this->toArray($file);
                        if(!empty($data) && count($data) == 1) {
                            if(file_exists($file) && $this->test === false) { unlink($file); }
                        }
                    }
                }
                $print_count = count(glob($input.$this->getSession().'main-*.json'));
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Filtered grouped files count: ".$print_count.PHP_EOL);
            }
        }
    }

    private function step3_description_match(string $input): void {
        $search = glob($input.$this->getSession().'main-*.json');
        if(!empty($search)) {
            foreach($search as $file) {
                $filename = preg_replace("/_main-/", "_grp1_1-", $file);
                $data = $this->toArray($file);
                $arr = [];
                foreach($data as $item) {
                    $md5 = null;
                    if(array_key_exists('description', $item)) {
                        $md5 = md5(serialize([
                            'file' => $file,
                            'description' => substr($item['description'], 0, $this->getOptions()['description_short_length']),
                        ]));
                        if(!empty($md5)) {
                            $item['file'] = $file;
                            $arr[$md5][] = $item;
                        }
                    }
                }
                if(!empty($arr)) {
                    foreach($arr as $k => $v) {
                        if(count($v) == 1) {
                            unset($arr[$k]);
                        }
                    }
                }
                if(!empty($arr)) {
                    $this->save($arr, $filename);
                } else {
                    if(file_exists($filename) && $this->test === false) { unlink($filename); }
                }

                // TODO check if impacts system performance during run
                // $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                // if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
            }
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Data grouped by description #hash:".PHP_EOL);

            $search = glob($input.$this->getSession().'grp1_1-*.json');
            if(!empty($search)) {
                foreach($search as $file) {
                    $data = $this->toArray($file);
                    foreach($data as $key => $value) {
                        $filename = $input.$this->getSession().'grp1_2-'.$key.'.json';
                        $grp_search = glob($filename);
                        if(!empty($grp_search) && isset($grp_search[0])) {
                            $dt = $this->toArray($filename);
                            $dt = array_merge($dt, $value);
                            if(!empty($dt)) { $this->save($dt, $filename); }
                        } else {
                            if(!empty($value)) { $this->save($value, $filename); }
                        }
                    }
                    if(file_exists($file) && $this->test === false) { unlink($file); }

                    $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                    if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                }
                $print_count = count(glob($input.$this->getSession().'grp1_2-*.json'));
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Saved sub-grouped files count:".$print_count.PHP_EOL);

                $search = glob($input.$this->getSession().'grp1_2-*.json');
                if(!empty($search)) {
                    foreach($search as $file) {
                        $data = $this->toArray($file);
                        if(!empty($data) && count($data) == 1) {
                            if(file_exists($file) && $this->test === false) { unlink($file); }
                        }
                    }
                }
                $print_count = count(glob($input.$this->getSession().'grp1_2-*.json'));
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Filtered sub-grouped files count: ".$print_count.PHP_EOL);
            }
        }
    }

    private function step4_process_by_description(string $input): void {
        $search = glob($input.$this->getSession().'grp1_2-*.json');
        if(!empty($search)) {
            $i = 0;
            foreach($search as $file) {
                $i++;
                $data = $this->toArray($file);

                // order by date desc
                $dates = array_column($data, 'date');
                array_multisort($dates, SORT_DESC, $data);

                $ids = array_column($data, 'id');

                foreach($data as $key => $value) {
                    if(array_key_exists('description', $value) && !empty($value['description'])) {
                        $description = $this->sanitize($this->getSlug()->get($this->getStop()->setCountry($this->getCountry())->get($value['description'])));
                        if(!empty($description)) { $data[$key]['description'] = $description; }
                        $data[$key]['duplicates_temp'] = [];
                    } else { unset($data[$key]); }
                }

                foreach($data as $key_x => $value_x) {
                    if($data[$key_x]['processed'] == true) { continue; }
                    $arr = $data;
                    unset($arr[$key_x]);
                    foreach($arr as $key_y => $value_y) {
                        if($value_y['processed'] == true) { continue; }
                        $data[$key_x]['processed'] = true;
                        $fuzz1 = null;
                        $fuzz1 = $this->getFuzz()->get($value_x['description'], $value_y['description']);
                        if(!empty($fuzz1) && isset($fuzz1['score'])) {
                            if($fuzz1['score'] >= $this->getOptions()['description_score']) {
                                $data[$key_x]['duplicates_temp'] = $this->pushDuplicates($data[$key_y]['id'], $data[$key_x]['duplicates_temp']);
                                $data[$key_y]['processed'] = true;
                            }
                        }
                    }
                }

                // reset processed
                foreach($data as $key => $value) { $data[$key]['processed'] = false; }

                foreach($data as $key => $value) {
                    if($data[$key]['processed'] == true) { continue; }
                    if($data[$key]['is_main'] == true && count($data[$key]['duplicates_temp']) > 0) {
                        $data[$key]['processed'] = true;
                        foreach($value['duplicates_temp'] as $id) {
                            $duplicates_d = array_diff($value['duplicates_temp'], [$id]);
                            $key_d = array_search($id, $ids);
                            $data[$key_d]['processed'] = true;
                            $data[$key_d]['duplicates_temp'] = array_unique(array_merge([$value['id']], $duplicates_d), SORT_REGULAR);
                        }
                    }
                }

                // reset processed
                foreach($data as $key => $value) { $data[$key]['processed'] = false; }

                foreach($data as $key => $value) {
                    if($data[$key]['processed'] == true) { continue; }
                    if(empty($value['duplicates_temp'])) {
                        $data[$key]['processed'] = true;
                    } else {
                        $data[$key]['processed'] = true;
                        $data[$key]['duplicates'] = array_unique(array_merge($value['duplicates'], $value['duplicates_temp']), SORT_REGULAR);
                        foreach($value['duplicates_temp'] as $id) {
                            $key_d = array_search($id, $ids);
                            $data[$key_d]['processed'] = true;
                            $data[$key_d]['is_main'] = false;
                            $data[$key_d]['duplicates'] = array_unique(array_merge($data[$key_d]['duplicates'], $data[$key_d]['duplicates_temp']), SORT_REGULAR);
                        }
                    }
                }

                foreach($data as $key => $value) {
                    $arr = $this->toArray($value['file']);
                    foreach($arr as $a_key => $a_value) {
                        if($a_value['id'] == $value['id']) {
                            $arr[$a_key]['duplicates'] = $value['duplicates'];
                            $arr[$a_key]['is_main'] = $value['is_main'];
                            $arr[$a_key]['to_update'] = true;
                        }
                    }
                    $this->save($arr, $value['file']);

                    $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                    if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                }

                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s.u')." Processing file: ".$i.' / '.count($search).PHP_EOL);

                if(file_exists($file) && $this->test === false) { unlink($file); }
            }
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Finished description matching".PHP_EOL);
        }
    }

    private function step5_name_match(string $input): void {
        $search = glob($input.$this->getSession().'main-*.json');
        if(!empty($search)) {
            foreach($search as $file) {
                $filename = preg_replace("/_main-/", "_grp2_1-", $file);
                $data = $this->toArray($file);
                $arr = [];
                foreach($data as $item) {
                    $md5 = null;
                    if($item['is_main'] === true) {
                        $md5 = md5(serialize([
                            'file' => $file,
                            'name' => substr($item['name'], 0, $this->getOptions()['name_length']),
                        ]));
                        if(!empty($md5)) {
                            $item['file'] = $file;
                            $arr[$md5][] = $item;
                        }
                    }
                }
                if(!empty($arr)) {
                    foreach($arr as $k => $v) {
                        if(count($v) == 1) {
                            unset($arr[$k]);
                        }
                    }
                }
                if(!empty($arr)) {
                    $this->save($arr, $filename);
                } else {
                    if(file_exists($filename) && $this->test === false) { unlink($filename); }
                }

                // TODO check if impacts system performance during run
                // $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                // if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
            }
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Data grouped by name #hash:".PHP_EOL);

            $search = glob($input.$this->getSession().'grp2_1-*.json');
            if(!empty($search)) {
                foreach($search as $file) {
                    $data = $this->toArray($file);
                    foreach($data as $key => $value) {
                        $filename = $input.$this->getSession().'grp2_2-'.$key.'.json';
                        $grp_search = glob($filename);
                        if(!empty($grp_search) && isset($grp_search[0])) {
                            $dt = $this->toArray($filename);
                            $dt = array_merge($dt, $value);
                            if(!empty($dt)) { $this->save($dt, $filename); }
                        } else {
                            if(!empty($value)) { $this->save($value, $filename); }
                        }
                    }
                    if(file_exists($file) && $this->test === false) { unlink($file); }

                    $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                    if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                }
                $print_count = count(glob($input.$this->getSession().'grp2_2-*.json'));
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Saved sub-grouped files count:".$print_count.PHP_EOL);

                $search = glob($input.$this->getSession().'grp2_2-*.json');
                if(!empty($search)) {
                    foreach($search as $file) {
                        $data = $this->toArray($file);
                        if(!empty($data) && count($data) == 1) {
                            if(file_exists($file) && $this->test === false) { unlink($file); }
                        }
                    }
                }
                $print_count = count(glob($input.$this->getSession().'grp2_2-*.json'));
                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Filtered sub-grouped files count: ".$print_count.PHP_EOL);
            }
        }
    }

    private function step6_process_by_name(string $input): void {
        $search = glob($input.$this->getSession().'grp2_2-*.json');
        if(!empty($search)) {
            $i = 0;
            foreach($search as $file) {
                $i++;
                $data = $this->toArray($file);

                // order by date desc
                $dates = array_column($data, 'date');
                array_multisort($dates, SORT_DESC, $data);

                $ids = array_column($data, 'id');

                foreach($data as $key => $value) {
                    if(isset($value['name']) && isset($value['description'])) {
                        $name = $this->sanitize($this->getSlug()->get($this->getStop()->setCountry($this->getCountry())->get($value['name'])));
                        $description = $this->sanitize($this->getSlug()->get($this->getStop()->setCountry($this->getCountry())->get($value['description'])));
                        if(!empty($description)) { $data[$key]['description'] = $description; }
                        if(!empty($name)) { $data[$key]['name'] = $name; }
                        $data[$key]['duplicates_temp'] = [];
                    } else { unset($data[$key]); }
                }

                foreach($data as $key_x => $value_x) {
                    if($data[$key_x]['processed'] == true) { continue; }
                    $arr = $data;
                    unset($arr[$key_x]);
                    foreach($arr as $key_y => $value_y) {
                        if($value_y['processed'] == true) { continue; }
                        $data[$key_x]['processed'] = true;
                        $fuzz1 = null;
                        $fuzz1 = $this->getFuzz()->get($value_x['name'], $value_y['name']);
                        if(!empty($fuzz1) && isset($fuzz1['score'])) {
                            if($fuzz1['score'] >= $this->getOptions()['name_score']) {
                                $fuzz2 = null;
                                $fuzz2 = $this->getFuzz()->get($value_x['description'], $value_y['description']);
                                if(!empty($fuzz2) && isset($fuzz2['score'])) {
                                    if($fuzz2['score'] >= $this->getOptions()['description_score']) {
                                        $data[$key_x]['duplicates_temp'] = $this->pushDuplicates($data[$key_y]['id'], $data[$key_x]['duplicates_temp']);
                                        $data[$key_y]['processed'] = true;
                                    }
                                }
                            }
                        }
                    }
                }

                // reset processed
                foreach($data as $key => $value) { $data[$key]['processed'] = false; }

                foreach($data as $key => $value) {
                    if($data[$key]['processed'] == true) { continue; }
                    if($data[$key]['is_main'] == true && count($data[$key]['duplicates_temp']) > 0) {
                        $data[$key]['processed'] = true;
                        foreach($value['duplicates_temp'] as $id) {
                            $duplicates_d = array_diff($value['duplicates_temp'], [$id]);
                            $key_d = array_search($id, $ids);
                            $data[$key_d]['processed'] = true;
                            $data[$key_d]['duplicates_temp'] = array_unique(array_merge([$value['id']], $duplicates_d), SORT_REGULAR);
                        }
                    }
                }

                // reset processed
                foreach($data as $key => $value) { $data[$key]['processed'] = false; }

                foreach($data as $key => $value) {
                    if($data[$key]['processed'] == true) { continue; }
                    if(empty($value['duplicates_temp'])) {
                        $data[$key]['processed'] = true;
                    } else {
                        $data[$key]['processed'] = true;
                        $data[$key]['duplicates'] = array_unique(array_merge($value['duplicates'], $value['duplicates_temp']), SORT_REGULAR);
                        foreach($value['duplicates_temp'] as $id) {
                            $key_d = array_search($id, $ids);
                            $data[$key_d]['processed'] = true;
                            $data[$key_d]['is_main'] = false;
                            $data[$key_d]['duplicates'] = array_unique(array_merge($data[$key_d]['duplicates'], $data[$key_d]['duplicates_temp']), SORT_REGULAR);
                        }
                    }
                }

                foreach($data as $key => $value) {
                    $arr = $this->toArray($value['file']);
                    foreach($arr as $a_key => $a_value) {
                        if($a_value['id'] == $value['id']) {
                            $arr[$a_key]['duplicates'] = $value['duplicates'];
                            $arr[$a_key]['is_main'] = $value['is_main'];
                            $arr[$a_key]['to_update'] = true;
                        }
                    }
                    $this->save($arr, $value['file']);

                    $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                    if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
                }

                $time = new DateTime('now');
                print('  '.$time->format('Y-m-d H:i:s.u')." Processing file: ".$i.' / '.count($search).PHP_EOL);

                if(file_exists($file) && $this->test === false) { unlink($file); }
            }

            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Finished name matching".PHP_EOL);
        }
    }

    private function step7_process_list(string $input): void {
        $search = glob($input.$this->getSession().'main-*.json');
        if(!empty($search)) {
            $arr = [];
            foreach($search as $file) {
                $data = $this->toArray($file);
                foreach($data as $item) {
                    if($item['to_update'] === true) {
                        $arr[] = [
                            'id' => $item['id'],
                            'duplicates' => $item['duplicates'],
                            'is_main' => $item['is_main'],
                        ];
                    }
                }
                if(file_exists($file) && $this->test === false) { unlink($file); }
            }
        }

        $count = (int) count($arr);
        if($count > 0) {
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Affected rows count: ".$count.PHP_EOL);
            $i = 1;
            $j = (int) ceil($count / $this->getOptions()['transaction_size']);
            for($i; $i <= $j; $i++) {
                $length = $this->getOptions()['transaction_size'];
                $offset = ($length * $i) - $length;
                $data = array_slice($arr, $offset, $length);
                if(!empty($data)) {
                    $filename = $input.$this->getSession().'list-'.$this->leadingZeros($i,6).'.json';
                    $this->save($data, $filename);
                }

                $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
            }
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Lists saved".PHP_EOL);
        }
    }

    private function step8_update_database(string $input): void {
        $repo = $this->getEntityManager()->getRepository(Property::class);

        $search = glob($input.$this->getSession().'list-*.json');
        if(!empty($search)) {
            foreach($search as $file) {
                $data = $this->toArray($file);

                foreach($data as $item) {
                    if($property = $repo->find($item['id'])) {
                        $property->setDuplicates($item['duplicates']);
                        $property->setIsMain($item['is_main']);
                        $this->getEntityManager()->persist($property);
                    } else {
                        $data = [
                            'message' => ['not_found' => $item['id']],
                            'logger' => get_called_class().'::step5'
                        ];
                        $this->getLogger()->setData($data);
                    }
                }

                try {
                    $this->getEntityManager()->flush();
                } catch(ORMException $e) {
                    $data = [
                        'message' => [
                            'description' => 'Cannot update database records',
                            'details' => $e->getMessage()
                        ],
                        'logger' => get_called_class().'::step5'
                    ];
                    $this->getLogger()->setData($data);
                }

                if(file_exists($file) && $this->test === false) { unlink($file); }

                $delay = (empty($this->getOptions()['delay'])) ? 0 : $this->getOptions()['delay'] / 1000;
                if(!empty($delay)) { time_sleep_until(microtime(true) + $delay); }
            }
        }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function run(): void {
        $this->_init();

        $dir = $this->getWriter()['storage_path'].'duplicates'.DIRECTORY_SEPARATOR;

        $this->cleanup($dir);

        $this->step1_load_data($dir);
        $this->step2_main_filter_sort($dir);
        $this->step3_description_match($dir);
        $this->step4_process_by_description($dir);
        $this->step5_name_match($dir);
        $this->step6_process_by_name($dir);
        $this->step7_process_list($dir);
        $this->step8_update_database($dir);

        // cleanup
        if($this->test === true) { return; }
        foreach(glob($dir.$this->getSession().'*') as $file) {
            if(file_exists($file)) { unlink($file); }
        }
    }
}
