<?php

declare(strict_types=1);

namespace Scanner\Controller;

use Api\Entity\Property;
use App\Handler\AbstractAppTraitMethods;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Logger\Controller\LoggerController;
use Parser\Controller\ParserController;
use Psr\Container\ContainerInterface;
use \DateTime;
use \Exception;
use \GlobIterator;

class ScannerController {
    use AbstractAppTraitMethods;

    private ContainerInterface $container;
    private $data = [];
    private DateTime $date;
    private EntityManager $entity_manager;
    private $function = '';
    private $lock = '';
    private $loop = true;
    private LoggerController $logger;
    private ParserController $parser;
    private Property $property;
    private $scanner = [];
    private $session_id = '';
    private $targets = [];
    private $updater = false;
    private $writer = [];

    public function __construct(
        ContainerInterface $container,
        EntityManager $entity_manager,
        LoggerController $logger,
        ParserController $parser,
        array $scanner,
        array $targets,
        array $writer
    ) {
        $this->setContainer($container);
        $this->setEntityManager($entity_manager);
        $this->setLogger($logger);
        $this->setParser($parser);
        $this->setScanner($scanner);
        $this->setTargets($targets);
        $this->setWriter($writer);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getContainer(): ContainerInterface { return $this->container; }
    private function setContainer(ContainerInterface $input): void { $this->container = $input; }

    private function getData(): array { return $this->data; }
    private function setData(array $input): void { $this->data = $input; }

    private function getDate(): DateTime { return $this->date; }
    private function setDate(DateTime $input): void { $this->date = $input; }

    private function getEntityManager(): EntityManager { return $this->entity_manager; }
    private function setEntityManager(EntityManager $input): void { $this->entity_manager = $input; }

    private function getFunction(): string { return $this->function; }
    public function setFunction(string $input): self { $this->function = $input; return $this; }

    private function getLock(): string { return $this->lock; }
    private function setLock(string $input): void { $this->lock = $input; }

    private function getLoop(): bool { return $this->loop; }
    private function setLoop(bool $input): void { $this->loop = $input; }

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    private function getParser(): ParserController { return $this->parser; }
    private function setParser(ParserController $input): void { $this->parser = $input; }

    private function getProperty(): Property { return $this->property; }
    private function setProperty(Property $input): void { $this->property = $input; }

    private function getScanner(): array { return $this->scanner; }
    private function setScanner(array $input): void { $this->scanner = $input; }

    private function getSessionId(): string { return $this->session_id; }
    private function setSessionId(string $input): void { $this->session_id = $input; }

    private function getTargets(): array { return $this->targets; }
    private function setTargets(array $input): void { $this->targets = $input; }

    private function getUpdater(): bool { return $this->updater; }
    public function setUpdater(bool $input): self { $this->updater = $input; return $this; }

    private function getWriter(): array { return $this->writer; }
    private function setWriter(array $input): void { $this->writer = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        $this->setSessionId(uniqid());
        $this->setLoop(true);
        if($this->getFunction() == 'force') {
            $this->setUpdater(true);
        }
    }

    /**
     * This checks configuration options.
     * To be passed without Exception.
     *
     * @throws Exception With detailed problem description.
     */
    private function configCheck(): void {
        if(empty($this->getScanner())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `scanner` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `scanner` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getScanner()['compare']) || empty($this->getScanner()['compare'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `compare` attribute from `scanner` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `compare` attribute from `scanner` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getScanner()['extension']) || empty($this->getScanner()['extension'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `extension` attribute from `scanner` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `extension` attribute from `scanner` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getScanner()['limit']) || empty($this->getScanner()['limit'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `limit` attribute from `scanner` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `limit` attribute from `scanner` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getScanner()['list_file']) || empty($this->getScanner()['list_file'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `list_file` attribute from `scanner` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `list_file` attribute from `scanner` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getScanner()['timeout']) || empty($this->getScanner()['timeout'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `timeout` attribute from `scanner` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `timeout` attribute from `scanner` configuration from `config/autoload/global.php`");
        }
        if(empty($this->getTargets())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawler_targets` configuration from target modules `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `crawler_targets` configuration from target modules `ConfigProvider.php`");
        }
        if(empty($this->getWriter())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `writer` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getWriter()['storage_path']) || empty($this->getWriter()['storage_path'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `storage_path` attribute from `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `storage_path` attribute from `writer` configuration from `config/autoload/global.php`");
        }
        if(!isset($this->getWriter()['extension']) || empty($this->getWriter()['extension'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `extension` attribute from `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `storage_path` attribute from `writer` configuration from `config/autoload/global.php`");
        }
    }

    private function create(array $input, bool $precheck = true, bool $filter = true): void {
        // prevent duplicates
        if($precheck === true) {
            $id = $this->preCheck($input);
            if(!empty($id)) {
                $input['id'] = $id;
                $input['modified'] = $input['created'];
                unset($input['created']);
                $input['is_active'] = true;
                $input['is_main'] = true;
                $this->update($input);
                return;
            }
        }

        // skip rubbish (missing main data)
        if($filter === true) {
            if($this->filterCheck($input)) {
                if($this->getLogger()->getDebug()['log']['create']) {
                    $data = [
                        'message' => [
                            'description' => 'Property not created',
                            'details' => 'missing-main-data'
                        ],
                        'url' => $input['origin_url'],
                        'logger' => get_called_class().'::create'
                    ];
                    $this->getLogger()->setData($data);
                }
                return;
            }
        }

        $input = $this->escHtml($input);

        $this->setProperty(new Property());
        try {
            $this->getProperty()->setProperty($input);
            $this->getEntityManager()->persist($this->getProperty());
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            $data = [
                'message' => [
                    'description' => 'Property not created',
                    'details' => $e->getMessage()
                ],
                'logger' => get_called_class().'::create'
            ];
            $this->getLogger()->setData($data);
        }
    }

    private function escHtml(array $input): array {
        $attributes = [
            'description',
            'name'
        ];
        foreach($attributes as $attr) {
            if(isset($input[$attr]) && !empty($input[$attr])) {
                $input[$attr] = $this->escQuotes($input[$attr]);
            }
        }
        return $input;
    }

    private function filterCheck(array $input): bool {
        $return = false;
        if(
            empty($input['category'])
            && empty($input['score'])
            && empty($input['type'])
            || (isset($input['errors']) && $input['errors'] === true)

        ) { $return = true; }
        return $return;
    }

    private function lock(string $input): void {
        $list = $this->scan($input);
        $list_file = $input.$this->getLock().$this->getScanner()['list_file'];
        foreach($list as $item) {
            $new = $input.$this->getLock().$item.'.'.$this->getScanner()['extension'];
            $old = $input.$item;
            if(file_exists($old)) {
                rename($old, $new);
                chmod($new, octdec($this->getLogger()->getPermissions()['file']));
            }
        }
        $this->save($list, $list_file);
    }

    private function postCheck(string $input, array $item, array $parser, array $array): bool {
        if(empty($parser)) {
            $data = [
                'message' => ['Empty response from `Parser`'],
                'url' => $item['file'],
                'logger' => get_called_class().'::postCheck'
            ];
            $this->getLogger()->setData($data);
            if(file_exists($input.$item['file'])) { unlink($input.$item['file']); }
            return false;
        }

        if(!isset($parser['name'])) {
            $data = [
                'message' => [
                    'description' => 'Parse error',
                    'details' => [
                        "Missing required `name`",
                        'matcher_class' => $array['matcher_class']
                    ]
                ],
                'url' => $item['file'],
                'logger' => get_called_class().'::postCheck'
            ];
            $this->getLogger()->setData($data);
            if(file_exists($input.$item['file'])) { unlink($input.$item['file']); }
            return false;
        }

        if(!isset($parser['origin_url'])) {
            $data = [
                'message' => [
                    'description' => 'Parse error',
                    'details' => [
                        "Missing required `origin_url`",
                        'matcher_class' => $array['matcher_class']
                    ]
                ],
                'url' => $item['file'],
                'logger' => get_called_class().'::postCheck'
            ];
            $this->getLogger()->setData($data);
            if(file_exists($input.$item['file'])) { unlink($input.$item['file']); }
            return false;
        }

        if(!isset($parser['origin_updated'])) {
            $data = [
                'message' => [
                    'description' => 'Parse error',
                    'details' => [
                        "Missing required `origin_updated`",
                        'matcher_class' => $array['matcher_class']
                    ]
                ],
                'url' => $item['file'],
                'logger' => get_called_class().'::postCheck'
            ];
            $this->getLogger()->setData($data);
            if(file_exists($input.$item['file'])) { unlink($input.$item['file']); }
            return false;
        }

        /**
         * Special case handling.
         * Avoid app crash if matcher_class is not valid for target.
         * Skip and continue with next target.
         * Valid pattern: 'Example\Word\Class'
         */
        if(isset($array['matcher_class']) && preg_match("/(?:[A-Z]{1}\w+\/){2}(?:[A-Z]{1}\w+){1}/", str_replace("\\", "/", $array['matcher_class']))) {
            // Skip if targets matcher_class doesn't exists
            if(!$this->getContainer()->has($array['matcher_class'])) {
                $data = [
                    'message' => [
                        'description' => 'Unregistered class name',
                        'details' => "Unregistered class name: `{$array['matcher_class']}`"
                    ],
                    'logger' => get_called_class().'::postCheck'
                ];
                $this->getLogger()->setData($data);
                return false;
            }
        } else {
            $data = [
                'message' => [
                    'description' => 'Invalid class name',
                    'details' => "Invalid class name: `{$array['matcher_class']}`"
                ],
                'logger' => get_called_class().'::postCheck'
            ];
            $this->getLogger()->setData($data);
            return false;
        }

        if(!isset($array['country']) || empty($array['country'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `country` attribute from `crawler_targets->target` configuration from `ConfigProvider.php`"
                ],
                'logger' => get_called_class().'::postCheck'
            ];
            $this->getLogger()->setData($data);
            return false;
        }

        return true;
    }

    private function preCheck(array $input): ?string {
        $return = null;
        if(isset($input['origin_id']) && !empty($input['origin_id'])) {
            $return = '';
            $repository = $this->getEntityManager()->getRepository(Property::class);
            $query = $repository
                ->createQueryBuilder('p')
                ->where('p.origin_id = :origin_id')
                ->setParameter('origin_id', $input['origin_id']);
            $data = $query->getQuery()->getResult();
            if(!empty($data)) { $return = $data[0]->getProperty()['id']; }
        }
        return $return;
    }

    private function process(string $input, array $array): void {
        // prevent duplicates
        $data = [];
        $list = [];
        $this_list = [];
        $this_list_file = '';
        $unique = [];

        $search = glob($input.'*'.$this->getScanner()['list_file']);
        if(!empty($search)) {
            foreach($search as $item) {
                if(str_contains($item, $this->getLock()) === false) {
                    $data = $this->toArray($item);
                    if(!empty($data)) {
                        $list = array_merge($list, $data);
                    }
                } else {
                    $this_list = $this->toArray($item);
                    $this_list_file = $item;
                }
            }
        }

        $search = [
            "/^(?:".$this->getWriter()['filename_prefix']."_)/",
            "/(?:\.".$this->getWriter()['extension'].")$/",
            "/(?:_\d{8}_\d{6})/",
            "/(?:_)/"
        ];
        $replace = ['', '', '', '/'];

        if(!empty($list)) {
            foreach($list as $item) {
                $string = preg_replace($search, $replace, $item);
                preg_match($this->getTargets()[$this->getData()['target']]['pattern_short'], $string, $id);
                if(isset($id[1]) && !empty($id[1])) {
                    $unique[] = $id[1];
                }

            }
        }

        $unique = array_unique($unique, SORT_REGULAR);
        sort($unique);

        $return = [];
        if(!empty($this_list)) {
            foreach($this_list as $item) {
                $string = preg_replace($search, $replace, $item);
                preg_match($this->getTargets()[$this->getData()['target']]['pattern_short'], $string, $id);
                if(isset($id[1]) && !empty($id[1])) {
                    $return[] = ['id' => $id[1], 'filename' => $item];
                }

            }
        }

        if(!empty($return)) {
            foreach($return as $item) {
                $id_exists = array_search($item['id'], $unique);
                if($id_exists !== false) {
                    $new = $input.$item['filename'];
                    $old = $input.$this->getLock().$item['filename'].'.'.$this->getScanner()['extension'];
                    if(file_exists($old)) {
                        rename($old, $new);
                        chmod($new, octdec($this->getLogger()->getPermissions()['file']));
                    }
                }
            }
        }

        // compare filtered locked files with db_dump and process them
        $compare = [];
        foreach(glob($input.$this->getLock().$this->getScanner()['compare']) as $file) {
            $compare[] = $file;
        }

        if(empty($compare)) {
            $data = [
                'message' => [
                    'description' => 'Missing file',
                    'details' => $input.$this->getLock().$this->getScanner()['compare']
                ],
                'logger' => get_called_class().'::process'
            ];
            $this->getLogger()->setData($data);
            if(file_exists($this_list_file)) { unlink($this_list_file); }
            return;
        }
        $compare_file = $compare[0];
        $compare = $this->toArray($compare_file);
        $files = [];
        foreach($this->scanLocked($input, $this->getLock(), $this->getScanner()['extension']) as $item) {
            $parser = [];
            if(file_exists($input.$item['file'])) {
                $parser = $this->getParser()
                               ->setTarget($input.$item['file'])
                               ->setParsingMap($array['parser']['process'])
                               ->get();
            }

            if(!$this->postCheck($input, $item, $parser, $array)) { continue; }

            $matcher = $this->getContainer()->get($array['matcher_class']);
            $matcher_updated = $matcher->setCountry($array['country'])
                                        ->setData($parser)
                                        ->getUpdated();
            $filetime = new DateTime($this->filenameDate($item['file'], "_", "\."));
            $persist = [];

            $id = $matcher_updated['origin_id'];
            $id_exists = array_search($id, array_column($compare, 'origin_id'));

            // already exists
            if($id_exists !== false) {
                $persist['id'] = $compare[$id_exists]['id'];

                // it isn't active
                if(!$compare[$id_exists]['is_active']) {
                    $persist['is_active'] = true;
                    $persist['modified'] = $filetime;
                    $matcher_all = $matcher->setCountry($array['country'])
                                            ->setData($parser)
                                            ->get();
                    $persist = array_merge($persist, $matcher_all);
                    $this->update($persist);

                // it's active
                } else {
                    if($this->getUpdater()) {
                        $persist['modified'] = $filetime;
                        $matcher_all = $matcher->setCountry($array['country'])
                                                ->setData($parser)
                                                ->get();
                        $persist = array_merge($persist, $matcher_all);
                        $this->update($persist);
                    } else {
                        $date_compare = $compare[$id_exists]['origin_updated'];
                        if(is_array($date_compare) && isset($date_compare['date']) && isset($date_compare['timezone'])) {
                            $date_compare = $date_compare['date'].' '.$date_compare['timezone'];
                        }
                        $date_compare = new DateTime($date_compare);

                        // it has been updated since last known update
                        if($date_compare < $matcher_updated['origin_updated']) {
                            $persist['modified'] = $filetime;
                            $matcher_all = $matcher->setCountry($array['country'])
                                                    ->setData($parser)
                                                    ->get();
                            $persist = array_merge($persist, $matcher_all);
                            $this->update($persist);
                        }
                    }
                }
            }

            // new item, doesn't existed before
            else {
                if($this->getUpdater() && $this->getFunction() != 'force') {
                    $data = [
                        'message' => [
                            'description' => 'Record not found during update',
                            'details' => $item['file']
                        ],
                        'logger' => get_called_class().'::process'
                    ];
                    $this->getLogger()->setData($data);
                } else {
                    $persist['created'] = $filetime;
                    $matcher_all = $matcher->setCountry($array['country'])
                                            ->setData($parser)
                                            ->get();
                    $persist = array_merge($persist, $matcher_all);
                    $this->create($persist);
                }
            }
            if(file_exists($input.$item['file'])) { unlink($input.$item['file']); }
            $this->counter++;
        }
        if(file_exists($this_list_file)) { unlink($this_list_file); }
    }

    private function scan(string $input): array {
        $iterator = new GlobIterator($input.'*.'.$this->getWriter()['extension']);
        $return = [];
        $counter = 0;
        foreach ($iterator as $item) {
            $pattern = "/_(?:listings-|check_|pagination_)/";
            if(!preg_match($pattern, $item->getFilename())) {
                $return[] = $item->getFilename();
                $counter++;
                if($counter == $this->getScanner()['limit']) {
                    break;
                }
            }
        }
        if(empty($return)) { $this->setLoop(false); }
        return $return;
    }

    private function scanLocked(string $path, string $patter_start, string $pattern_end): array {
        $iterator = new GlobIterator($path.$patter_start.'*'.$pattern_end);
        $return = [];
        foreach ($iterator as $item) {
            $file = $item->getFilename();
            $locktime = $this->filenameDate($file, "_", "_");
            $unlock = $this->isTimeout($locktime, $this->getScanner()['timeout']);
            $return[] = [
                'file' => $file,
                'locktime' => $locktime,
                'unlock' => $unlock
            ];
        }
        return $return;
    }

    private function unlock(string $input): void {
        foreach($this->scanLocked($input, '', $this->getScanner()['compare']) as $item) {
            if($item['unlock'] && $item['file'] != $this->getLock().$this->getScanner()['compare']) {
                $file = $input.$item['file'];
                if(file_exists($file)) { unlink($file);}
            }
        }
        foreach($this->scanLocked($input, '', $this->getScanner()['list_file']) as $item) {
            if($item['unlock'] && $item['file'] != $this->getLock().$this->getScanner()['list_file']) {
                $file = $input.$item['file'];
                if(file_exists($file)) { unlink($file);}
            }
        }
        foreach($this->scanLocked($input, '', $this->getScanner()['extension']) as $item) {
            if($item['unlock']) {
                $file = $item['file'];
                $pattern = "/(.*)(".$this->getWriter()['filename_prefix'].".*)\.".$this->getScanner()['extension']."$/";
                preg_match($pattern, $file, $match);
                if(!empty($match) && isset($match[2])) {
                    $new = $input.$match[2];
                    $old = $input.$file;
                    if(file_exists($old)) {
                        rename($old, $new);
                        chmod($new, octdec($this->getLogger()->getPermissions()['file']));
                    }
                }
            }
        }
    }

    private function update(array $input): void {
        $input = $this->escHtml($input);
        $repo = $this->getEntityManager()->getRepository(Property::class);
        $this->setProperty($repo->find($input['id']));
        if(!empty($this->getProperty())) {
            $this->getProperty()->setProperty($input);
            try {
                $this->getEntityManager()->persist($this->getProperty());
                $this->getEntityManager()->flush();
            } catch(ORMException $e) {
                $data = [
                    'message' => [
                        'description' => 'Cannot update Property',
                        'details' => $e->getMessage()
                    ],
                    'logger' => get_called_class().'::update'
                ];
                $this->getLogger()->setData($data);
            }

        } else {
            $data = [
                'message' => ['not_found' => $input['id']],
                'logger' => get_called_class().'::update'
            ];
            $this->getLogger()->setData($data);
        }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function fetch(string $input, int $limit = 0, ?int $offset = 0, string $order = 'desc'): array {
        $repository = $this->getEntityManager()->getRepository(Property::class);
        $query = $repository->createQueryBuilder('p')
            ->select(['p.id', 'p.is_active', 'p.origin_id', 'p.origin_url', 'p.origin_updated', 'p.modified'])
            ->orderBy('p.is_private', 'DESC')
            ->addOrderBy('p.origin_updated', strtoupper($order));
        $query->andWhere(
            $query->expr()->andX(
                $query->expr()->eq('p.is_active', ':is_active'),
                $query->expr()->eq('p.origin_host', ':origin_host'),
                $query->expr()->isNotNull('p.category'),
                $query->expr()->isNotNull('p.country'),
                $query->expr()->isNotNull('p.type'),
                // $query->expr()->isNull('p.archived'),
            )
        )
        ->setParameter('is_active', true)
        ->setParameter('origin_host', $input);
        if($limit > 0) {
            $query->setMaxResults($limit);
        }
        if($offset > 0) {
            $query->setFirstResult($offset);
        }

        $return = $query->getQuery()->getResult();
        return $return;
    }

    public function list(string $input, int $limit = 0, ?int $offset = 0): array {
        $repository = $this->getEntityManager()->getRepository(Property::class);
        $query = $repository->createQueryBuilder('p')->select(['p.id', 'p.is_active', 'p.origin_id', 'p.origin_url', 'p.origin_updated', 'p.modified']);

        $and = $query->expr()->andX();
        $and->add($query->expr()->eq('p.origin_host', $query->expr()->literal($input)));
        $and->add($query->expr()->eq('p.is_active', $query->expr()->literal('1')));
        if(!empty($this->getFunction())) {
            if($this->getFunction() == 'origin_url') {
                $and->add($query->expr()->notLike('p.origin_url', $query->expr()->literal('http%')));
            }
        }
        if(!empty($this->getFunction())) {
            if($this->getFunction() == 'contact_name') {
                $and->add($query->expr()->isNull('p.contact_name'));
            }
        }
        $query->where($and);

        $query->orderBy('p.is_private', 'DESC')
              ->addOrderBy('p.origin_updated', 'DESC');

        if($limit > 0) {
            $query->setMaxResults($limit);
        }
        if($offset > 0) {
            $query->setFirstResult($offset);
        }

        $return = $query->getQuery()->getResult();
        return $return;
    }

    public function run(string $target_name = ''): void {
        $this->_init();
        $data = [];
        if(!empty($target_name)) {
            $target = [];
            foreach($this->getTargets() as $key => $value) {
                if($value["crawler_class"] == $target_name) {
                    $target[$key] = $value;
                }
            }
            if(!empty($target)) {
                $this->setTargets($target);
            }
        }

        foreach($this->getTargets() as $key => $value) {
            $this->setData(['target' => $key]);
            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." Scanner is processing: ".$key.PHP_EOL);
            $base_url = parse_url($value['crawl_url'][0], PHP_URL_HOST);
            $base_dir = $this->getWriter()['storage_path'].$key.DIRECTORY_SEPARATOR;

            $this->counter = 0;

            while ($this->getLoop()) {
                $this->setDate(new DateTime('now'));
                $this->setLock($this->getSessionId().'_'.$this->getDate()->format('Ymd_His').'_');

                $compare = ($this->getUpdater()) ? $this->list($base_url) : $this->fetch($base_url);
                $compare_filename = $base_dir.$this->getLock().$this->getScanner()['compare'];
                $this->save($compare, $compare_filename);

                $this->unlock($base_dir);
                $this->lock($base_dir);
                $this->process($base_dir, $value);

                if(file_exists($compare_filename)) { unlink($compare_filename); }
            }

            $time = new DateTime('now');
            print('  '.$time->format('Y-m-d H:i:s')." ".$this->getSessionId()." # Scanner counter # ".$this->counter.PHP_EOL);

            // cleanup
            foreach(glob($base_dir.$this->getSessionId().'*'.$this->getScanner()['compare']) as $file) {
                if(file_exists($file)) { unlink($file); }
            }
            foreach(glob($base_dir.$this->getSessionId().'*'.$this->getScanner()['list_file']) as $file) {
                if(file_exists($file)) { unlink($file); }
            }
        }
    }

    public function updateIsActive(string $id, bool $state = false): void {
        $repo = $this->getEntityManager()->getRepository(Property::class);
        $this->setProperty($repo->find($id));
        if(!empty($this->getProperty())) {
            if($state === false) {
                $this->getProperty()->setArchived();
            }
            $this->getProperty()->setIsActive($state);

            try {
                $this->getEntityManager()->persist($this->getProperty());
                $this->getEntityManager()->flush();
            } catch(ORMException $e) {
                $data = [
                    'message' => [
                        'description' => 'Cannot update Property',
                        'details' => $e->getMessage()
                    ],
                    'logger' => get_called_class().'::updateIsActive'
                ];
                $this->getLogger()->setData($data);
            }

        } else {
            $data = [
                'message' => ['not_found' => $id],
                'logger' => get_called_class().'::updateIsActive'
            ];
            $this->getLogger()->setData($data);
        }
    }
}
