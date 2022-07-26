<?php

declare(strict_types=1);

namespace Services\Helper;

use \Exception;

/*
 * Library to work with Slovak addresses.
 *
 * 2021-03-10T12:00:00+01:00
 *
 * Usage:
 *
 *     Functions:
 *       customDataset('file_path') // use reset() beforehand
 *       getAll()
 *       getOneResult()
 *       getResults()
 *       reset()
 *       search('Košice, Štúrova ulica', 'town')
 *       setCountry('sk')
 *       strictSearch('Košice, Štúrova ulica', 'street')
 *
 *     View some results:
 *       $address = $address->setCountry('sk')->search('Košice, Štúrova ulica', 'town')->getOneResult();
 *       var_dump($address);
 */
class AddressHelper {
    use AbstractServicesTraitClass;

    public function __construct(array $datasets, $logger, $slug) {
        $this->setDatasets($datasets);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->configCheck();
    }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init(): void {
        if(!$this->countryCodeCheck()) { return; }

        $this->dfs = 'address_'.$this->getCountry().'.json';
        $this->dir = $this->getDatasets()['address'][$this->getCountry()]['base_path'];

        if(empty($this->getData())) {
            if(empty($this->getDatasetFile())) {
                $this->setDatasetFile($this->dir.$this->dfs);
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

    private function getBy(string $input, string $target): void {
        if(!is_string($input) || !is_string($target)) { return; }
        if($target == 'postcode' || $target == 'longitude' || $target == 'latitude') {
            if(strlen($input) < 3) { return; }
            $slug = [ $input ];
            $x = '';
        } else {
            $input = $this->slugify($input);
            if(strlen($input) < 3) { return; }
            $slug = $this->getUniqueSearchString($input, true);
            $x = '_slug';
        }
        $return = [];
        foreach($this->getData() as $item) {
            foreach($slug as $search) {
                if(true == stristr($item[$target.$x], $search)) {
                    $return[] = $item;
                    continue 2;
                }
            }
        }
        if(!empty($return)) $this->setData($return);

        // double filter, to match the whole address field in search string
        $this->getStrictBy($input, $target);
    }

    private function getStrictBy(string $input, string $target, bool $mode = false): void {
        if(!is_string($input) || !is_string($target)) { return; }
        if($target == 'postcode' || $target == 'longitude' || $target == 'latitude') {
            if(strlen($input) < 3) { return; }
            $x = '';
        } else {
            $input = $this->slugify($input);
            if(strlen($input) < 3) { return; }
            $x = '_slug';
        }
        $return = [];
        foreach($this->getData() as $item) {
            // compare from beginning of the words
            if($mode == true) {
                if(0 == substr_compare($input, $item[$target.$x], 0, strlen($item[$target.$x]))) {
                    $return[] = $item;
                }
            }

            // free compare
            else {
                if(true == stristr($item[$target.$x], $input)) {
                    $return[] = $item;
                }
            }
        }
        if(!empty($return)) $this->setData($return);
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function reset(): self {
        $this->setData([]);
        return $this;
    }

    public function search(string $search, string $target): self {
        $this->_init();
        $this->getBy($search, $target);
        return $this;
    }

    public function strictSearch(string $search, string $target, bool $mode = false): self {
        $this->_init();
        $this->getStrictBy($search, $target, $mode);
        return $this;
    }
}
