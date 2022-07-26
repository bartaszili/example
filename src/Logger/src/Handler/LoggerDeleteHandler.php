<?php

declare(strict_types=1);

namespace Logger\Handler;

use Api\Handler\AbstractApiTraitClass;
use Doctrine\ORM\ORMException;
use Laminas\Diactoros\Response\JsonResponse;
use Logger\Entity\Logs;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggerDeleteHandler implements RequestHandlerInterface {
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

        $id = $request->getAttribute('id', null);

        $entity = $this->getEntityRepository()->find($id);

        if(empty($entity)) {
            $error[] = ['not_found' => $id];
            if(!empty($error)) { $this->rsp = ['error' => $error]; }
            return new JsonResponse($this->rsp, 404);
        }

        try {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            $error['id'] = $id;
            $error['message'] = 'cannot_remove';
            $error['description'] = $e->getMessage();
            if(!empty($error)) { $this->rsp = ['error' => $error]; }
            return new JsonResponse($this->rsp, 500);
        }

        $msg['removed'] = $id;
        if(!empty($msg)) { $this->rsp = ['success' => $msg]; }

        return new JsonResponse($this->rsp);
    }
}
