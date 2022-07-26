<?php

declare(strict_types=1);

namespace Api\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

trait AbstractApiTraitClass {
    use AbstractApiTraitMethods;

    private EntityManager $entity_manager;
    private EntityRepository $entity_repository;
    private HalResponseFactory $hal_response_factory;
    private $page_count = 15;
    private $request = [];
    private ResourceGenerator $resource_generator;
    private $valid_tokens = [];

    ///////////////////////////////////////
    ////////// Getters & Setters //////////
    ///////////////////////////////////////

    private function getEntityManager(): EntityManager { return $this->entity_manager; }
    private function setEntityManager(EntityManager $input): void { $this->entity_manager = $input; }

    private function getEntityRepository(): EntityRepository { return $this->entity_repository; }
    private function setEntityRepository(EntityRepository $input): void { $this->entity_repository = $input; }

    private function getHalResponseFactory(): HalResponseFactory { return $this->hal_response_factory; }
    private function setHalResponseFactory(HalResponseFactory $input): void { $this->hal_response_factory = $input; }

    private function getPageCount(): int { return $this->page_count; }
    private function setPageCount(int $input): void { $this->page_count = $input; }

    private function getRequest(): array { return $this->request; }
    private function setRequest(array $input): void { $this->request = $input; }

    private function getResourceGenerator(): ResourceGenerator { return $this->resource_generator; }
    private function setResourceGenerator(ResourceGenerator $input): void { $this->resource_generator = $input; }

    private function getValidTokens(): array { return $this->valid_tokens; }
    private function setValidTokens(array $input): void { $this->valid_tokens = $input; }

}
