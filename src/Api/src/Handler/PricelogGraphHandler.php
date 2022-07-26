<?php

declare(strict_types=1);

namespace Api\Handler;

use Api\Entity\Pricelog;
use Api\Entity\Property;
use Api\Entity\Collection\PricelogCollection;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PricelogGraphHandler implements RequestHandlerInterface {
    use AbstractApiTraitClass;

    private $rsp = [];
    private $rqst_main = 'Request';
    private $rqst_sub = 'Pricelog';

    public function __construct(
        $entity_manager,
        $hal_response_factory,
        $resource_generator,
        array $valid_tokens,
        int $page_count
    ) {
        $this->SetEntityManager($entity_manager);
        $this->SetHalResponseFactory($hal_response_factory);
        $this->SetResourceGenerator($resource_generator);
        $this->SetValidTokens($valid_tokens);
        $this->SetPageCount($page_count);
    }

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function checkGroupBy(): bool {
        $return = true;
        $error = [];
        $pattern = "/(?:year|month|week|day)/";
        if(!isset($this->getRequest()['group_by'])) {
            $error[] = ['missing_attribute' => 'group_by'];
            $return = false;
        } elseif(empty($this->getRequest()['group_by'])) {
            $error[] = ['empty_attribute' => 'group_by'];
            $return = false;
        } elseif(!preg_match($pattern, (string) $this->getRequest()['group_by'])) {
            $error[] = ['invalid_value' => "group_by"];
            $return = false;
        }
        if(!empty($error)) { $this->rsp = ['error' => $error]; }
        return $return;
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

        if(!$this->checkGroupBy()) {
            return new JsonResponse($this->rsp, 400);
        }

        $this->setEntityRepository($this->getEntityManager()->getRepository(Pricelog::class));

        $query = $this->getEntityRepository()
                      ->createQueryBuilder('pl')
                      ->leftJoin(Property::class, 'p', 'with', 'pl.property_id = p.id');

        $this->filter($query, 'p');

        $query->select('AVG(pl.price) as average_price')
              ->addSelect('MIN(pl.price) as min_price')
              ->addSelect('MAX(pl.price) as max_price');

        switch(strtolower($this->getRequest()['group_by'])) {
            case "day":
                $date_format = '%Y-%m-%d';
                break;
            case "week":
                $date_format = '%V';
                break;
            case "year":
                $date_format = '%Y';
                break;
            default:
                $date_format = '%Y-%m';
        }

        $query->addSelect('DATE_FORMAT(pl.created, :date_format) as dateGroup')
                ->groupBy('dateGroup')
                ->setParameter('date_format', $date_format);

        $paginator = new PricelogCollection($query->getQuery());

        $resource = $this->getResourceGenerator()->fromArray($paginator->toArray());
        return $this->getHalResponseFactory()->createResponse($request, $resource);
    }
}
