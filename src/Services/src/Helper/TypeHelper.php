<?php

declare(strict_types=1);

namespace Services\Helper;

/*
 * Acts as cross-reference sorter to determine Ads types
 *
 * 2021-02-10T20:45:00+01:00
 *
 *  Usage:
 *
 *      Functions: *
 *          customDataset('file_path')
 *          getAll()
 *          getOneResult()
 *          getResults()
 *          search('string')
 *          setCountry('sk')
 *
 *      View some results:
 *          $type = $type->setCountry('sk')->search('predaj')->getOneResult();
 *          var_dump($type);
 */
class TypeHelper {
    use AbstractServicesTraitClass;

    private $dsf = 'type.json';

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
        $this->loadDataset();
    }

    private function getByI18n(string $input): void {
        if (!is_string($input)) { return; }
        $input = $this->slugify($input);
        if(strlen($input) < 3) { return; }
        $slug = $this->getUniqueSearchString($input);
        $return = [];
        foreach ($this->getData() as $item) {
            foreach($slug as $search) {
                if (true == stristr($item[$this->getCountry().'_slug'], $search)) {
                    $return[] = $item;
                    break 2;
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
        $this->getByI18n($search);
        return $this;
    }
}
