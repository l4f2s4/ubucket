<?php

namespace App\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Repo;
use App\Entity\User;
use App\Entity\GroupT;
use Symfony\Component\Process\Process;

class RepoService {
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function create(User $user, Repo $repo)
    {

        $repo->setUserId($user);

        // Add DB info
        $manager = $this->doctrine->getManagerForClass(get_class($repo));
        $manager->persist($repo);
        $manager->flush();

        $gitPath = $this->getGitPath($repo);
        @mkdir($gitPath, 0777, true);

        $process = new Process(['git', '--git-dir='.$gitPath, 'init', '--bare']);
        $process->run();
        

        return true;
    }
    public function createGroupRepos(User $user,GroupT $group, Repo $repo)
    {

        $repo->setUserId($user);
        $repo->setGroupId($group);

        // Add DB info
        $manager = $this->doctrine->getManagerForClass(get_class($repo));
        $manager->persist($repo);
        $manager->flush();

        $gitPath = $this->getGitPath($repo);
        @mkdir($gitPath, 0777, true);

        $process = new Process(['git', '--git-dir='.$gitPath, 'init', '--bare']);
        $process->run();
        

        return true;
    }

    public function getGitPath(Repo $repo)
    {
        return '/repos'.'/'.strtolower($repo->getUserId()->getUsername()).'/'.strtolower($repo->getName()).'.git';
    }

}
