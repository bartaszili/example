<?php

declare(strict_types=1);

namespace Api\Handler;

use App\Handler\AbstractAppTraitMethods;
use Doctrine\ORM\QueryBuilder;
use Psr\Http\Message\ServerRequestInterface;

trait AbstractApiTraitMethods {
    use AbstractAppTraitMethods;

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function checkRequest(ServerRequestInterface $request): bool {
        $return = true;
        $error = [];
        if(empty($request->getParsedBody())) {
            $error[] = ['missing_request' => 'No request body sent'];
            $return = false;
        }
        if(!isset($request->getParsedBody()[$this->rqst_main])) {
            $error[] = ['missing_attribute' => $this->rqst_main];
            $return = false;
        } elseif(empty($request->getParsedBody()[$this->rqst_main])) {
            $error[] = ['empty_attribute' => $this->rqst_main];
            $return = false;
        }
        if(!isset($request->getParsedBody()[$this->rqst_main][$this->rqst_sub])) {
            $error[] = ['missing_attribute' => $this->rqst_sub];
            $return = false;
        } elseif(empty($request->getParsedBody()[$this->rqst_main][$this->rqst_sub])) {
            $error[] = ['empty_attribute' => $this->rqst_sub];
            $return = false;
        }
        if(!empty($error)) { $this->rsp = ['error' => $error]; }
        return $return;
    }

    private function checkToken(): bool {
        $return = true;
        $error = [];
        if(!isset($this->getRequest()['token'])) {
            $error[] = ['missing_attribute' => 'token'];
            $return = false;
        } elseif(empty($this->getRequest()['token'])) {
            $error[] = ['empty_attribute' => 'token'];
            $return = false;
        }
        if((array_search($this->getRequest()['token'], array_column($this->getValidTokens(), 'token')) === false)) {
            $error[] = ['access_validation' => 'forbidden'];
            $return = false;
        }
        if(!empty($error)) { $this->rsp = ['error' => $error]; }
        return $return;
    }

    private function filter(QueryBuilder $query, $alias = 'p'): void {
        if(isset($this->getRequest()['filter']) || !empty($this->getRequest()['filter'])) {
            foreach($this->getRequest()['filter'] as $key => $value) {
                if(
                    !empty($value)
                    && (substr_compare($key, '_min', -4, 4, true) != 0)
                    && (substr_compare($key, '_max', -4, 4, true) != 0)
                    && (substr_compare($key, '_not', -4, 4, true) != 0)
                    && (substr_compare($key, '_like', -5, 5, true) != 0)
                    && (substr_compare($key, '_btw', -4, 4, true) != 0)
                ) {
                    if(count($value) > 1) {
                        $or = $query->expr()->orX();
                        foreach($value as $item) {
                            $or->add($query->expr()->eq($alias.'.'.$key, $query->expr()->literal($item)));
                        }
                    } elseif(count($value) == 1) {
                        $or = $query->expr()->eq($alias.'.'.$key, $query->expr()->literal($value[0]));
                    }
                    $query->andWhere($or);
                }

                // LIKE
                if(!empty($value) && (substr_compare($key, '_like', -5, 5, true) == 0)) {
                    $key = substr($key, 0, -5);
                    if(count($value) > 1) {
                        $or = $query->expr()->orX();
                        foreach($value as $item) {
                            $or->add($query->expr()->like($alias.'.'.$key, $query->expr()->literal('%'.$item.'%')));
                        }
                    } elseif(count($value) == 1) {
                        $or = $query->expr()->like($alias.'.'.$key, $query->expr()->literal('%'.$value[0].'%'));
                    }
                    $query->andWhere($or);
                }

                // NOT LIKE
                if(!empty($value) && (substr_compare($key, '_not', -4, 4, true) == 0)) {
                    $key = substr($key, 0, -4);
                    if(count($value) > 1) {
                        $or = $query->expr()->orX();
                        foreach($value as $item) {
                            $or->add($query->expr()->notLike($alias.'.'.$key, $query->expr()->literal('%'.$item.'%')));
                        }
                    } elseif(count($value) == 1) {
                        $or = $query->expr()->notLike($alias.'.'.$key, $query->expr()->literal('%'.$value[0].'%'));
                    }
                    $query->andWhere($or);
                }

                // MIN
                if(substr_compare($key, '_min', -4, 4, true) == 0) {
                    $key = substr($key, 0, -4);
                    $query->andWhere(
                        $query->expr()->andX(
                            $query->expr()->isNotNull($alias.'.'.$key),
                            $query->expr()->gte($alias.'.'.$key, $query->expr()->literal($value))
                        )
                    );
                }

                // MAX
                if(substr_compare($key, '_max', -4, 4, true) == 0) {
                    $key = substr($key, 0, -4);
                    $query->andWhere(
                        $query->expr()->orX(
                            $query->expr()->isNull($alias.'.'.$key),
                            $query->expr()->lte($alias.'.'.$key, $query->expr()->literal($value))
                        )
                    );
                }

                // BETWEEN
                if(substr_compare($key, '_btw', -4, 4, true) == 0) {
                    $key = substr($key, 0, -4);
                    $query->andWhere(
                        $query->expr()->between($alias.'.'.$key, $query->expr()->literal($value['min']), $query->expr()->literal($value['max']))
                    );
                }
            }
        }
    }
}
