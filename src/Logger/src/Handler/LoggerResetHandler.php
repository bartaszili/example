<?php

declare(strict_types=1);

namespace Logger\Handler;

use Api\Handler\AbstractApiTraitClass;
use Doctrine\ORM\ORMException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggerResetHandler implements RequestHandlerInterface {
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

        try {
            $connection = $this->getEntityManager()->getConnection();
            $platform   = $connection->getDatabasePlatform();
            $connection->executeUpdate($platform->getTruncateTableSQL('logs', false));
        } catch(ORMException $e) {
            $error['message'] = 'cannot_turnaculate_table';
            $error['description'] = $e->getMessage();
            if(!empty($error)) { $this->rsp = ['error' => $error]; }
            return new JsonResponse($this->rsp, 500);
        }

        $msg['turnaculated'] = 'logs';
        if(!empty($msg)) { $this->rsp = ['success' => $msg]; }

        return new JsonResponse($this->rsp);
    }
}
