<?php

declare(strict_types=1);

namespace Logger\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use Laminas\Stdlib\SplPriorityQueue;
use Logger\Entity\Logs;
use \Exception;
use \Error;

/**
 * Logger module main controller
 *
 * Acts as a logger servise.
 * By default it logs into `logs` database table.
 * As a fallback will log into `/data/logs/*.log` files.
 * Change configuration in `/config/autoload/global.php`.\
 * \
 * Expects an `array(message, url, logger, token)`.\
 * `message` = log message.\
 * `url` = related url.\
 * `logger` = namespace path extended with class and method, where the log was issued.\
 * `token` = valid token.\ *
 */
class LoggerController extends Logger
{
    /** array(message, url, class_method) */
    private $data = [];
    private $debug = [];
    private EntityManager $entityManager;
    private $filename = '';
    private Logs $logs;
    private $options = [];
    private $permissions = [];

    public function __construct(EntityManager $entityManager, array $debug, array $options, array $permissions) {
        $this->setDebug($debug);
        $this->setEntityManager($entityManager);
        $this->setOptions($options);
        $this->setPermissions($permissions);
        parent::__construct();
        $this->setFilename($this->getOptions()['filename'].'.'.$this->getOptions()['extension']);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getData(): array { return $this->data; }
    public function setData(array $input): void { $this->data = $input; if(!empty($this->getData())) { $this->create(); } }

    public function getDebug(): array { return $this->debug; }
    private function setDebug(array $input): void { $this->debug = $input; }

    private function getEntityManager(): EntityManager { return $this->entityManager; }
    private function setEntityManager(EntityManager $input): void { $this->entityManager = $input; }

    private function getFilename(): string { return $this->filename; }
    public function setFilename(string $input): void { $this->filename = $input; $this->reInitWrite(); }

    private function getLogs(): Logs { return $this->logs; }
    private function setLogs(Logs $input): void { $this->logs = $input; }

    private function getOptions(): array { return $this->options; }
    private function setOptions(array $input): void { $this->options = $input; }

    public function getPermissions(): array { return $this->permissions; }
    private function setPermissions(array $input): void { $this->permissions = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    /**
     * This is the main function.
     * After successfull token validation it exchanges token to hostname.
     * Next JSON encode message.
     * Than it tries to insert this into database.
     * If that fails, it will try to log into file instead.
     * If that fails we skip and ignore it silently.
     */
    private function create(): void {
        $this->setLogs(new Logs());

        try {
            $this->getLogs()->setLog($this->getData());
            $this->getEntityManager()->persist($this->getLogs());
            $this->getEntityManager()->flush();

        } catch (ORMException $e) {
            $message = [
                'error' => $e->getMessage(),
                'message' => $this->getData()['message']
            ];
            $this->data['message'] = $message;

            try {
                $this->err(json_encode($this->getData()));

            } catch (Error $e) {
                // Skip silently
            }
        }
    }

    /**
     * This checks configuration options.
     * To be passed without Exception.
     *
     * @throws Exception With detailed problem description.
     */
    private function configCheck(): void {
        if(empty($this->getFilename())) { $this->setFilename('error.log'); }

        if(empty($this->getOptions())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `logger` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `logger` configuration");
        }

        if(!isset($this->getOptions()['storage_path']) || empty($this->getOptions()['storage_path'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `storage_path` attribute from `logger` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `storage_path` attribute from `logger` configuration");
        }

        if(!isset($this->getOptions()['filename']) || empty($this->getOptions()['filename'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `filename` attribute from `logger` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `filename` attribute from `logger` configuration");
        }

        if(!isset($this->getOptions()['extension']) || empty($this->getOptions()['extension'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `extension` attribute from `logger` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `extension` attribute from `logger` configuration");
        }

        if(empty($this->getPermissions())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `permissions` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `permissions` configuration");
        }

        if(!isset($this->getPermissions()['directory']) || empty($this->getPermissions()['directory'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `directory` attribute from `permissions` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `directory` attribute from `permissions` configuration");
        }

        if(!isset($this->getPermissions()['file']) || empty($this->getPermissions()['file'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `file` attribute from `permissions` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `file` attribute from `permissions` configuration");
        }

        if(empty($this->getDebug())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `debug` configuration");
        }

        if(!isset($this->getDebug()['is_active'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `is_active` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `is_active` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['log']) || empty($this->getDebug()['log'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `log` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `log` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['log']['crawled'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `crawled` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `crawled` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['log']['short_filename'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `short_filename` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `short_filename` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['log']['should_crawl'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `shouldCrawl` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `shouldCrawl` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['log']['persist'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `persist` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `persist` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['log']['create'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `create` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `create` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['console_log']) || empty($this->getDebug()['console_log'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `console_log` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `console_log` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['console_log']['crawler'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `console_log->crawler` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `console_log->crawler` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['console_log']['matcher'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `console_log->matcher` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `console_log->matcher` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['scanner'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `scanner` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `scanner` attribute from `debug` configuration");
        }

        if(!isset($this->getDebug()['short_filename'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `short_filename` attribute from `debug` configuration from `config/autoload/debug.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->err(json_encode($data));
            throw new Exception("Missing `short_filename` attribute from `debug` configuration");
        }
    }

    /** Initialize Write Stream */
    private function reInitWrite(): void {
        $storage = $this->getOptions()['storage_path'].$this->getFilename();
        $this->writers = new SplPriorityQueue;
        $this->addWriter(new Stream($storage));
    }
}
