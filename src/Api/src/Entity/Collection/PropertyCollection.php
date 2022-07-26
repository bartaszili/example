<?php

declare(strict_types=1);

namespace Api\Entity\Collection;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PropertyCollection extends Paginator {
    public function __construct($query) {
        parent::__construct($query);
    }

    // TODO not finished. To get pagination, needs to return different Object type
    public function toObject() {
        $return = [];
        foreach ($this->getQuery()->getResult() as $item) {
            $item[0]->setDistance($item['distance']);
            $return[] = $item[0];
        }
        return $return;
    }
}
