<?php

declare(strict_types=1);

namespace Services\Helper;

/*
 * 2021-02-10T21:30:00+01:00
 *
 * Library to work with ISO 4217 currency data.
 *
 *
 * Data source:
 *  https://github.com/tammoippen/iso4217parse/blob/master/iso4217parse/data.json
 *
 *
 *  Usage:
 *
 *      Functions:
 *          getAll()
 *          getOneResult()
 *          getResults()
 *          search('string') // can be currency symbol, alpha2 country code, alpha3 or numeric currency code
 *
 *      View some results:
 *          $currency = $currency->search("€")->getOneResult();
 *          var_dump($currency);
 */
class CurrencyHelper {
    use AbstractServicesTraitClass;

    private $dsf = 'currency.json';

    public function __construct(array $datasets, $logger, $slug) {
        $this->setDatasets($datasets);
        $this->setLogger($logger);
        $this->setSlug($slug);
        $this->configCheck();
    }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function _init() {
        $this->loadDataset();
    }

    private function getByCode($input) {
        if(!is_string($input) || (mb_strlen($input) != 3)) { return; }
        $return = [];
        foreach($this->getData() as $item) {
            if(0 === strcasecmp($input, $item['alpha3']) || 0 === strcasecmp($input, (string) $item['code_num'])) {
                $return[] = $item;
            }
        }
        if(!empty($return)) $this->setData($return);
    }

    private function getByCountry($input) {
        if(!is_string($input)) { return; }
        $return = [];
        foreach($this->getData() as $item) {
            foreach($item['countries'] as $value) {
                if(0 === strcasecmp($input, $value)) {
                    $return[] = $item;
                }
            }
        }
        if(!empty($return)) $this->setData($return);
    }

    private function getBySymbol($symbol) {
        if(!is_string($symbol)) { return; }
        $return = [];
        foreach($this->getData() as $item) {
            foreach($item['symbols'] as $value) {
                if(0 === strcasecmp($symbol, $value)) {
                    $return[] = $item;
                }
            }
        }
        if(!empty($return)) $this->setData($return);
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function search(string $search): self {
        $this->_init();
        $this->getBySymbol($search);
        $this->getByCountry($search);
        $this->getByCode($search);
        return $this;
    }
}
