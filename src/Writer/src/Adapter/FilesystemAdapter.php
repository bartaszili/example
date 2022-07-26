<?php

declare(strict_types=1);

namespace Writer\Adapter;

use Logger\Controller\LoggerController;
use \DateTime;
use \Exception;

/** Save crawled html data as files. */
class FilesystemAdapter {
    private $path = '';
    private LoggerController $logger;

    public function __construct(string $path) {
        $this->setPath($path);
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getLogger(): LoggerController { return $this->logger; }
    public function setLogger(LoggerController $input): void { $this->logger = $input; }

    public function getPath(): string { return $this->path; }
    private function setPath(string $input): void { $this->path = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    /** Appends filename string with formatted DateTime. */
    private function appendDate(string $filename): string {
        $pathinfo = pathinfo($filename);
        return $pathinfo['filename'].(new DateTime('now'))->format('_Ymd_His').'.'.$pathinfo['extension'];
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    /**
     * Main method to save crawled html data to filesystem.\
     *
     * @param string $content
     * @param string $filename
     * @throws \Exception if $filename is not set
     * @throws \Exception if script has no writing permissions
     */
    public function persist(string $content, string $filename) {
        if(!$filename) {
            $data = [
                'message' => [
                    'description' => 'Missing parameter',
                    'details' => "Filename not set!"
                ],
                'logger' => get_called_class().'::persist'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Filename not set!");
        }
        if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['persist']) {
            $debug = $filename;
            $data = [
                'message' => ['debug init'],
                'url' => $debug,
                'logger' => get_called_class().'::persist'
            ];
            $this->getLogger()->setData($data);
        }
        $filename = $this->sanitize($filename);
        $filename = $this->appendDate($filename);
        if(!is_dir($this->getPath())) { mkdir($this->getPath(), octdec($this->getLogger()->getPermissions()['directory']), true); }
        chmod($this->getPath(), octdec($this->getLogger()->getPermissions()['directory']));

        if(!$handle = fopen($this->getPath().$filename, 'w+')) {
            $data = [
                'message' => [
                    'description' => 'Permission error',
                    'details' => "Can't open file '".$this->getPath().$filename."' for writing!"
                ],
                'logger' => get_called_class().'::persist'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Can't open file '".$this->getPath().$filename."' for writing!");
        }
        fwrite($handle, $content);
        fclose($handle);
        chmod($this->getPath().$filename, octdec($this->getLogger()->getPermissions()['file']));
        if($this->getLogger()->getDebug()['is_active'] && $this->getLogger()->getDebug()['log']['persist']) {
            $data = [
                'message' => ['debug close'],
                'url' => $debug,
                'logger' => get_called_class().'::persist'
            ];
            $this->getLogger()->setData($data);
        }
    }

    /** Sanitize filename transforms in our case url string to an acceptable filename format. */
    public function sanitize(?string $filename): string {
        $filename = str_replace('/', '_', $filename);
        $filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        $filename = preg_replace("([\.]{2,})", '', $filename);
        return $filename;
    }
}
