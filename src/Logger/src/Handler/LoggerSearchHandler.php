<?php

declare(strict_types=1);

namespace Logger\Handler;

use Api\Handler\AbstractApiTraitClass;
use Laminas\Diactoros\Response\JsonResponse;
use Logger\Entity\Collection\LogsCollection;
use Logger\Entity\Logs;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggerSearchHandler implements RequestHandlerInterface {
    use AbstractApiTraitClass;

    private $rsp = [];
    private $rqst_main = 'Request';
    private $rqst_sub = 'Logs';

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

        $this->setEntityRepository($this->getEntityManager()->getRepository(Logs::class));

        $query = $this->getEntityRepository()->createQueryBuilder('p');

        $this->filter($query, 'p');

        $this->setPageCount((isset($this->getRequest()['page_limit']) && !empty($this->getRequest()['page_limit'])) ? $this->getRequest()['page_limit'] : $this->getPageCount());
        $query = $query->addOrderBy('p.created','desc')->addOrderBy('p.logger','asc')->setMaxResults($this->getPageCount());

        $paginator = new LogsCollection($query->getQuery());
        $resource = $this->getResourceGenerator()->fromObject($paginator, $request);

        return $this->getHalResponseFactory()->createResponse($request, $resource);
    }
}
