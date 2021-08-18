<?php

namespace App\DataObject;

use App\Entity\Repo as RepositoryEntity;
use App\Service\GitService;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Repository {

    /** @var RepositoryEntity */
    protected $entity;

    /** @var \App\Service\GitService */
    protected $gitService;

    /** @var \Symfony\Component\PropertyAccess\PropertyAccessor */
    protected $accessor;

    function __construct(RepositoryEntity $entity, GitService $gitService)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $this->entity = $entity;
        $this->gitService = $gitService;
    }

    function getTotalCommits()
    {
        return $this->gitService->getTotalCommits();
    }

    function getContributors()
    {
        return $this->gitService->getContributors();
    }

    function getCommitters($branch)
    {
        return $this->gitService->getCommitters($branch);
    }

    function getGraph()
    {
        return $this->gitService->getGraph();
    }
    function getCommit($hash)
    {
        return $this->gitService->getCommit($hash);
    }
    public function getStatistics($branch){
        return $this->gitService->getStatistics($branch);
    }
    public function getAuthorStatistics($branch){
        return $this->gitService->getAuthorStatistics($branch);
    }

    function __get($property)
    {
        return $this->accessor->getValue($this->entity, $property);
    }

    function __isset($property)
    {
        return $this->accessor->isReadable($this->entity, $property);
    }

}
