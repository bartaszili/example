<?php

declare(strict_types=1);

namespace Api\Handler;

use Api\Entity\Pricelog;
use Api\Entity\Collection\PricelogCollection;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PricelogSearchHandler implements RequestHandlerInterface {
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

        $this->setEntityRepository($this->getEntityManager()->getRepository(Pricelog::class));

        // Include or not related property in response
        $recursive = false;
        if (isset($this->getRequest()['recursive']) && !empty($this->getRequest()['recursive'])) {
            $recursive = true;
        }

        $query = $this->getEntityRepository()->createQueryBuilder('pl');

        $this->filter($query, 'pl');

        $this->setPageCount((isset($this->getRequest()['page_limit']) && !empty($this->getRequest()['page_limit'])) ? $this->getRequest()['page_limit'] : $this->getPageCount());
        $query->addOrderBy('pl.created','asc')->setMaxResults($this->getPageCount());

        $paginator = new PricelogCollection($query->getQuery());
        $resource = $this->getResourceGenerator()->fromObject($paginator, $request);

        return $this->getHalResponseFactory()->createResponse($request, $resource);
    }
}
