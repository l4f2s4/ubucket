<?php

namespace App\Service;

use GitStash\CachableGit;
use GitStash\Git;
use App\Entity\Repo;
use App\Logger\GitLogger;
use App\Entity\User;
use Predis\Client;
use Doctrine\Common\Cache\RedisCache;

/**
 * Factory that allows us to instantiate gitService services
 *
 * Usage:
 *   $this->get('git_service_factory')->create(repository);
 */
class GitServiceFactory {

    /** @var Git */
    protected $git;

    /** @var GitLogger */
    protected $logger;

    /** @var \Predis\Client */
    protected $redis;

    /**
     * @param $basePath
     */
    function __construct(GitLogger $logger)
    {
        // $this->basePath = $basePath;
        $this->logger = $logger;
        // $this->redis = $redis;
    }

    /**
     * Instantiates a new git service. Assumes that repository name and username are all lowercase.
     *
     * @param Repos $repo
     * @return GitService
     */
    function create(Repo $repo) {
        $path = '/repos'.'/'.strtolower($repo->getUserId()->getUsername()).'/'.strtolower($repo->getName()).'.git';

         if (!is_dir($path)) {
             throw new \InvalidArgumentException('Repository path not found!');
         }


        $git = new CachableGit($path);
        $git->setRedis(new Client());
        $git->setLogger($this->logger);

        return new GitService($git);
    }

}
