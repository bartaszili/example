<?php

declare(strict_types=1);

namespace Services\Helper;

use \Exception;

class StopWordsHelper {
    use AbstractServicesTraitClass;

    private $keywords = [];
    private $keywords_file = '';

    public function __construct(array $datasets, $logger, $slug) {
        $this->setDatasets($datasets);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->configCheck();
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getKeywords(): array { return $this->keywords; }
    private function setKeywords(array $input): void { $this->keywords = $input; }

    private function getKeywordsFile(): string { return $this->keywords_file; }
    private function setKeywordsFile(string $input): void { $this->keywords_file = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        if(!$this->countryCodeCheck()) { return; }

        $this->kwf = 'stop_words_'.$this->getCountry().'.json';
        $this->dir = $this->getDatasets()['shared']['i18n_path'].$this->getCountry().DIRECTORY_SEPARATOR;

        if(empty($this->getKeywordsFile())) {
            $this->setKeywordsFile($this->dir.$this->kwf);
        }

        if(file_exists($this->getKeywordsFile())) {
            $string = file_get_contents($this->getKeywordsFile());
            $array = json_decode($string, true);
            if(!isset($array['dataset'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `dataset` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `dataset` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            if(!isset($array['dataset']['_keywords'])) {
                $data = [
                    'message' => [
                        'description' => 'Missing attribute',
                        'details' => "Missing `_keywords` attribute from dataset file `{$this->getKeywordsFile()}`"
                    ],
                    'logger' => get_called_class().'::_init'
                ];
                $this->getLogger()->setData($data);
                throw new Exception("Missing `_keywords` attribute from dataset file `{$this->getKeywordsFile()}`");
            }
            $this->setKeywords($array['dataset']['_keywords']);
        } else {
            $data = [
                'message' => [
                    'description' => 'Missing dataset file',
                    'details' => $this->getKeywordsFile()
                ],
                'logger' => get_called_class().'::_init'
            ];
            $this->getLogger()->setData($data);
            throw new Exception("Missing dataset file: `{$this->getKeywordsFile()}`");
        }
    }

    private function process(?string $input): ?string {
        if(empty($input)) { return ''; }
        $input = mb_strtolower($input);
        $pattern = "/\b(?:".implode('|', $this->getKeywords()).")\b/iu";
        return preg_replace($pattern, '', $input);
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function customKeywords(string $file): self {
        $this->setKeywordsFile($file);
        $this->_init();
        return $this;
    }

    public function get(?string $input): ?string {
        $this->_init();
        return $this->process($input);
    }
}
