<?php

declare(strict_types=1);

namespace Api\Entity\Collection;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PricelogCollection extends Paginator {
    public function __construct($query) {
        parent::__construct($query);
    }

    public function toArray() {
        $ret = [];
        foreach ($this->getQuery()->getArrayResult() as $key => $item) {
            $ret[$item['dateGroup']] = $item;
        }
        ksort($ret);
        return $ret;
    }
}
