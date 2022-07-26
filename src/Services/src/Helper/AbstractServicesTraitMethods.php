<?php

declare(strict_types=1);

namespace Services\Helper;

use App\Handler\AbstractAppTraitMethods;

trait AbstractServicesTraitMethods {
    use AbstractAppTraitMethods;

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function getUniqueSearchString(string $search, bool $length = false, bool $unique = true): array {
        $return = [];
        $array = ($unique) ? array_unique(explode(' ', $search)) : explode(' ', $search);
        $delete = [];
        foreach($array as $item) {
            if(strlen($item) >= 3) {
                foreach($array as $key => $value) {
                    if($item != $value) {
                        if(substr_count($value, $item) > 0) {
                            $delete[$key] = $value;
                        }
                    }
                }
            }
        }
        $diff = array_diff($array, $delete);
        foreach($diff as $item) {
            if($length) {
                if(strlen($item) > 2) {
                    $return[] = $item;
                }
            } else {
                $return[] = $item;
            }

        }
        arsort($return);
        reset($return);
        return $return;
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function customDataset(string $file): self {
        $this->setDatasetFile($file);
        $this->_init();
        return $this;
    }

    public function getAll(): ?array {
        $this->_init();
        return $this->getData();
    }

    public function getOneResult(): ?array {
        $return = null;
        if(isset($this->getData()[0])) { $return = $this->getData()[0]; }
        return $return;
    }

    public function getResults(): ?array {
        return $this->getData();
    }
}
