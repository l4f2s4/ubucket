<?php

namespace App\DataObject;

use App\Entity\Repo as RepositoryEntity;
use App\Service\GitServiceFactory;

class DataObjectFactory {

    /** @var GitServiceFactory */
    protected $gitServiceFactory;

    function __construct(GitServiceFactory $gitServiceFactory)
    {
        $this->gitServiceFactory = $gitServiceFactory;
    }

    function create($entity) {
        switch (true) {
            case $entity instanceof RepositoryEntity :
                return new Repository($entity, $this->gitServiceFactory->create($entity));
        }

        throw new \LogicException(sprintf("Cannot convert '%s' into a data object.", get_class($entity)));
    }

}
