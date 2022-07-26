<?php

declare(strict_types=1);

namespace Api\Handler;

use Api\Entity\Collection\PropertyCollection;
use Api\Entity\Property;
use Doctrine\ORM\QueryBuilder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Services\Helper\DistanceHelper;
// use Doctrine\ORM\Query\ResultSetMapping;

class PropertiesSearchHandler implements RequestHandlerInterface {
    use AbstractApiTraitClass;

    private DistanceHelper $distance;
    private $gps_dist = 25;
    private $gps_earth_radius = 6371.23;
    private $gps_lat = null;
    private $gps_lon = null;
    private $gps_sqr = null;
    private $gps_type = 'square';
    private $gps_unit = 'km';
    private $gps_unit_main = 'km';
    private $rsp = [];
    private $rqst_main = 'Request';
    private $rqst_sub = 'Properties';

    public function __construct(
        DistanceHelper $distance,
        $entity_manager,
        $hal_response_factory,
        $resource_generator,
        array $valid_tokens,
        int $page_count
    ) {
        $this->setDistance($distance);
        $this->SetEntityManager($entity_manager);
        $this->SetHalResponseFactory($hal_response_factory);
        $this->SetResourceGenerator($resource_generator);
        $this->SetValidTokens($valid_tokens);
        $this->SetPageCount($page_count);
    }

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getDistance(): DistanceHelper { return $this->distance; }
    private function setDistance(DistanceHelper $input): void { $this->distance = $input; }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function checkGps(): bool {
        $return = true;
        $error = [];
        $not_number = "/[^+\-\d\.]/";
        $pattern_types = "/(?:circle|square)/";
        $pattern_units = "/(?:mi|km|m|yd|ft)/";
        if(!isset($this->getRequest()['gps']['latitude'])) {
            $error[] = ['missing_attribute' => 'gps-latitude'];
            $return = false;
        } elseif(empty($this->getRequest()['gps']['latitude'])) {
            $error[] = ['empty_attribute' => 'gps-latitude'];
            $return = false;
        } elseif(preg_match($not_number, $this->formatFloat((string) $this->getRequest()['gps']['latitude']))) {
            $error[] = ['invalid_value' => "gps-latitude"];
            $return = false;
        } else {
            $this->gps_lat = $this->formatFloat((string) $this->getRequest()['gps']['latitude']);
        }
        if(!isset($this->getRequest()['gps']['longitude'])) {
            $error[] = ['missing_attribute' => 'gps-longitude'];
            $return = false;
        } elseif(empty($this->getRequest()['gps']['longitude'])) {
            $error[] = ['empty_attribute' => 'gps-longitude'];
            $return = false;
        } elseif(preg_match($not_number, $this->formatFloat((string) $this->getRequest()['gps']['longitude']))) {
            $error[] = ['invalid_value' => "gps-longitude"];
            $return = false;
        } else {
            $this->gps_lon = $this->formatFloat((string) $this->getRequest()['gps']['longitude']);
        }

        if(isset($this->getRequest()['gps']['distance']) && !empty($this->getRequest()['gps']['distance'])) {
            if(
                preg_match($not_number, $this->formatFloat((string) $this->getRequest()['gps']['distance']))
                || $this->getRequest()['gps']['distance'] < 1
            ) {
                $error[] = ['invalid_value' => "gps-distance"];
                $return = false;
            } else {
                $this->gps_dist = $this->formatFloat((string) $this->getRequest()['gps']['distance']);
            }
        }

        if(
            isset($this->getRequest()['gps']['type'])
            && !empty($this->getRequest()['gps']['type'])
            && !preg_match($pattern_types, $this->getRequest()['gps']['type'])
        ) {
            $error[] = ['invalid_value' => 'gps-type'];
            $return = false;
        } elseif(
            isset($this->getRequest()['gps']['type'])
            && !empty($this->getRequest()['gps']['type'])
            && preg_match($pattern_types, $this->getRequest()['gps']['type'])
        ) {
            $this->gps_type = (string) $this->getRequest()['gps']['type'];
        }
        if(
            isset($this->getRequest()['gps']['unit'])
            && !empty($this->getRequest()['gps']['unit'])
            && !preg_match($pattern_units, $this->getRequest()['gps']['unit'])
        ) {
            $error[] = ['invalid_value' => 'gps-unit'];
            $return = false;
        } elseif(
            isset($this->getRequest()['gps']['unit'])
            && !empty($this->getRequest()['gps']['unit'])
            && preg_match($pattern_units, $this->getRequest()['gps']['unit'])
        ) {
            $this->gps_unit = (string) $this->getRequest()['gps']['unit'];
        }
        if(!empty($error)) { $this->rsp = ['error' => $error]; }
        return $return;
    }

    private function order(QueryBuilder $query, $alias = 'p'): void {
        if(isset($this->getRequest()['order']) || !empty($this->getRequest()['order'])) {
            $pattern = "/(?:asc|desc)/i";
            $order = $this->getRequest()['order'];
            if(isset($order['first']) && !empty($order['first'])) {
                if(isset($order[$order['first']]) && preg_match($pattern, $order[$order['first']])) {
                    $query->orderBy($alias.'.'.$order['first'], strtoupper($order[$order['first']]));
                    unset($order[$order['first']]);
                }
            }
            if(isset($order['first'])) { unset($order['first']); }
            if(!empty($order)) {
                foreach($order as $key => $value) {
                    if(preg_match($pattern, $value)) {
                        $query->addOrderBy($alias.'.'.$key, strtoupper($value));
                    }
                }
            }
        } else {
            $query->orderBy($alias.'.origin_updated', 'DESC')
                  ->addOrderBy($alias.'.name', 'ASC');
        }
    }

    ////////////////////////////////////
    ////////// Public Methods //////////
    ////////////////////////////////////

    public function handle(ServerRequestInterface $request): ResponseInterface {
        if(!$this->checkRequest($request)) {
            return new JsonResponse($this->rsp, 400);
        } else {
            $this->setRequest($request->getParsedBody()[$this->rqst_main][$this->rqst_sub]);
        }

        if(!$this->checkToken()) {
            return new JsonResponse($this->rsp, 400);
        }

        $this->setEntityRepository($this->getEntityManager()->getRepository(Property::class));

        $query = $this->getEntityRepository()->createQueryBuilder('p');

        // set basic filtering criteria:
        $query->andWhere(
            $query->expr()->andX(
                $query->expr()->eq('p.is_active', ':is_active'),
                // $query->expr()->eq('p.is_main', ':is_main'), // provide this condition via request instead
                $query->expr()->isNotNull('p.category'),
                $query->expr()->isNotNull('p.country'),
                $query->expr()->isNotNull('p.type')

            )

        )
        ->setParameter('is_active', true)
        // ->setParameter('is_main', true)
        ;

        if(isset($this->getRequest()['gps']) && !empty($this->getRequest()['gps'])) {
            if(!$this->checkGps()) {
                return new JsonResponse($this->rsp, 400);
            }

            $radius = $this->getDistance()->from((string) $this->gps_unit_main)->to((string) $this->gps_unit)->value((string) $this->gps_earth_radius)->convertDistance();
            $distance = $this->getDistance()->from((string) $this->gps_unit)->to((string) $this->gps_unit_main)->value((string) $this->gps_dist)->convertDistance();

            switch($this->gps_type) {
                case 'square':
                    $this->gps_sqr = ($distance * 360 / (2 * M_PI * $radius)) / 2;
                    $query
                        ->andWhere('p.latitude >= :gps_lat_min')
                        ->andWhere('p.latitude <= :gps_lat_max')
                        ->andWhere('p.longitude >= :gps_lon_min')
                        ->andWhere('p.longitude <= :gps_lon_max')
                        ->setParameter('gps_lat_min', $this->gps_lat - $this->gps_sqr)
                        ->setParameter('gps_lat_max', $this->gps_lat + $this->gps_sqr)
                        ->setParameter('gps_lon_min', $this->gps_lon - $this->gps_sqr)
                        ->setParameter('gps_lon_max', $this->gps_lon + $this->gps_sqr)
                    ;
                    break;
                default:
                    $query
                        ->addSelect('(
                            :gps_radius
                            * acos(cos(radians(:gps_latitude))
                            * cos(radians(p.latitude))
                            * cos(radians(p.longitude)
                            - radians(:gps_longitude))
                            + sin(radians(:gps_latitude))
                            * sin(radians(p.latitude)))) AS distance')
                        ->having('distance < :gps_distance')
                        ->addOrderBy('distance','asc')
                        ->setParameter('gps_radius', $radius)
                        ->setParameter('gps_distance', $distance)
                        ->setParameter('gps_latitude', $this->gps_lat)
                        ->setParameter('gps_longitude', $this->gps_lon);
                    ;
            }
        }

        $this->filter($query, 'p');
        $this->order($query, 'p');

        if($this->gps_type != 'circle') {
            $this->setPageCount((isset($this->getRequest()['page_limit']) && !empty($this->getRequest()['page_limit'])) ? $this->getRequest()['page_limit'] : $this->getPageCount());
            $query->setMaxResults($this->getPageCount());
        } else {
            $return = $query->getQuery()->getArrayResult();
            return new JsonResponse($return);
        }

        $paginator = new PropertyCollection($query->getQuery());
        $resource = $this->getResourceGenerator()->fromObject($paginator, $request);

        // TODO objectize collection
        // $resource = $this->getResourceGenerator()->fromObject($paginator->toObject(), $request);

        return $this->getHalResponseFactory()->createResponse($request, $resource);
    }
}
