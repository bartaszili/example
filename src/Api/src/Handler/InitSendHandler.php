<?php

declare(strict_types=1);

namespace Api\Handler;

use Api\Entity\Property;
use Doctrine\ORM\QueryBuilder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InitSendHandler implements RequestHandlerInterface {
    use AbstractApiTraitClass;

    private $rsp = [];
    private $rqst_main = 'Request';
    private $rqst_sub = 'Init';

    public function __construct(
        $entity_manager,
        array $valid_tokens
    ) {
        $this->SetEntityManager($entity_manager);
        $this->SetValidTokens($valid_tokens);
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

        $data = [];
        $columns = [
            'category',
            // 'country',
            // 'county',
            // 'district',
            'origin_host',
            // 'postcode',
            // 'street',
            // 'town',
            // 'town_part',
            'type',
        ];
        foreach($columns as $target) {
            $query = $this->getEntityRepository()->createQueryBuilder('p');

            // set basic filtering criteria:
            $query->andWhere(
                $query->expr()->andX(
                    $query->expr()->eq('p.is_active', ':is_active'),
                    $query->expr()->isNotNull('p.category'),
                    $query->expr()->isNotNull('p.country'),
                    $query->expr()->isNotNull('p.type')
                )
            )
            ->setParameter('is_active', true)
            ->select('p.'.$target)
            ->groupBy('p.'.$target);

            $response = $query->getQuery()->getArrayResult();

            if(!empty($response)) {
                $data = array_merge($data, $response);
            }
        }

        $return = [];
        foreach($data as $item) {
            if(key($item) == 'category') { $return['category'][] = $item[key($item)]; }
            if(key($item) == 'country') { $return['country'][] = $item[key($item)]; }
            if(key($item) == 'county') { $return['county'][] = $item[key($item)]; }
            if(key($item) == 'district') { $return['district'][] = $item[key($item)]; }
            if(key($item) == 'origin_host') { $return['origin_host'][] = $item[key($item)]; }
            if(key($item) == 'postcode') { $return['postcode'][] = $item[key($item)]; }
            if(key($item) == 'street') { $return['street'][] = $item[key($item)]; }
            if(key($item) == 'town') { $return['town'][] = $item[key($item)]; }
            if(key($item) == 'town_part') { $return['town_part'][] = $item[key($item)]; }
            if(key($item) == 'type') { $return['type'][] = $item[key($item)]; }
        }

        return new JsonResponse($return);
    }
}
