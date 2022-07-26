<?php

declare(strict_types=1);

namespace Services\Helper;

use Logger\Controller\LoggerController;
use \Exception;

trait AbstractServicesTraitClass {
    use AbstractServicesTraitMethods;

    private $country = '';
    private $data = [];
    private array $datasets;
    private $dataset_file = '';
    private LoggerController $logger;
    private SlugifyHelper $slug;

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    public function getCountry(): string { return $this->country; }
    public function setCountry(string $input): self { $this->country = filter_var(strtolower(trim($input)), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH); return $this; }

    private function getData(): array { return $this->data; }
    private function setData(array $input): void { $this->data = $input; }

    public function getDatasets(): array { return $this->datasets; }
    private function setDatasets(array $input): void { $this->datasets = $input; }

    private function getDatasetFile(): string { return $this->dataset_file; }
    private function setDatasetFile(string $input): void { $this->dataset_file = filter_var(strtolower(trim($input)), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH); }

    private function getLogger(): LoggerController { return $this->logger; }
    private function setLogger(LoggerController $input): void { $this->logger = $input; }

    private function getSlug(): SlugifyHelper { return $this->slug; }
    private function setSlug(SlugifyHelper $input): void { $this->slug = $input; }

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
        if(empty($this->getDatasets())) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `datasets` configuration from `config/autoload/datasets.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `datasets` configuration from `config/autoload/datasets.global.php`");
        }
        if(!isset($this->getDatasets()['shared']) || empty($this->getDatasets()['shared'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `shared` attribute from `datasets` configuration from `config/autoload/datasets.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `shared` attribute from `datasets` configuration from `config/autoload/datasets.global.php`");
        }
        if(!isset($this->getDatasets()['shared']['i18n_path']) || empty($this->getDatasets()['shared']['i18n_path'])) {
            $data = [
                'message' => [
                    'description' => 'Missing configuration',
                    'details' => "Missing `shared->i18n_path` attribute from `datasets` configuration from `config/autoload/datasets.global.php`"
                ],
                'logger' => get_called_class().'::configCheck'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing `shared->i18n_path` attribute from `datasets` configuration from `config/autoload/datasets.global.php`");
        }
    }

    private function countryCodeCheck(): bool {
        if(empty($this->getCountry()) || !preg_match("/^[a-zA-Z]{2}$/", $this->getCountry())) {
            $data = [
                'message' => [
                    'description' => 'Silent log',
                    'details' => "Invalid country code: `{$this->getCountry()}`"
                ],
                'logger' => get_called_class().'::countryCodeCheck'
            ];
            $this->getLogger()->setData($data);
            return false;
        } else {
            return true;
        }
    }

    private function loadDataset(): void {
        $this->dir = $this->getDatasets()['shared']['base_path'];

        if(empty($this->getDatasetFile())) {
            $this->setDatasetFile($this->dir.$this->dsf);
        }

        if(file_exists($this->getDatasetFile())) {
            $string = file_get_contents($this->getDatasetFile());
            $array = json_decode($string, true);
            if(!isset($array['dataset'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `dataset` attribute from dataset file `{$this->getDatasetFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `dataset` attribute from dataset file `{$this->getDatasetFile()}`");
            }
            $this->setData($array['dataset']);
        } else {
            $data = [
                'message' => [
                    'description' => 'Missing dataset file',
                    'details' => $this->getDatasetFile()
                ],
                'logger' => get_called_class().'::_init'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing dataset file: `{$this->getDatasetFile()}`");
        }
    }
}
