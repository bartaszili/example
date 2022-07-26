<?php

declare(strict_types=1);

namespace Writer\Controller;

use Logger\Controller\LoggerController;
use Writer\Adapter\FilesystemAdapter;
use \Exception;

/**
 * Writer module main controller
 *
 * Acts as a writer servise.
 * By default it writes into `/data/tmp/[target_name]/[filename][timestamp].html` files.
 * Change configuration in `/config/autoload/global.php`.\
 * \
 * Expects strings `(data, filename)`.\
 * `data` = html body.\
 * `filename` = short url (hostname/id).
 */
class WriterController {
    private LoggerController $logger;
    private $options = [];
    private FilesystemAdapter $storage;

    public function __construct(LoggerController $logger, array $options) {
        $this->setLogger($logger);
        $this->setOptions($options);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    public function getOptions(): array { return $this->options; }
    private function setOptions(array $input): void { $this->options = $input; }

    public function getStorage(): FilesystemAdapter { return $this->storage; }
    public function setStorage(FilesystemAdapter $input): void { $this->storage = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

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
                    'details' => "Missing `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `writer` configuration");
        }
        if(!isset($this->getOptions()['extension']) || empty($this->getOptions()['extension'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `extension` attribute from `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `extension` attribute from `writer` configuration");
        }
        if(!isset($this->getOptions()['filename_prefix']) || empty($this->getOptions()['filename_prefix'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `filename_prefix` attribute from `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `filename_prefix` attribute from `writer` configuration");
        }
        if(!isset($this->getOptions()['id_prefix']) || empty($this->getOptions()['id_prefix'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `id_prefix` attribute from `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `id_prefix` attribute from `writer` configuration");
        }
        if(!isset($this->getOptions()['storage_path']) || empty($this->getOptions()['storage_path'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `storage_path` attribute from `writer` configuration from `config/autoload/global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `storage_path` attribute from `writer` configuration");
        }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    /** Write crawled `html` to disk. */
    public function writeRawData(string $data, string $filename): void {
        $this->getStorage()->setLogger($this->getLogger());
        $this->getStorage()->persist($data, $filename.'.'.$this->getOptions()['extension']);
    }
}
