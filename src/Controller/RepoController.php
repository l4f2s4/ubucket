<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use GitStash\Exception\ReferenceNotFoundException;
use App\Entity\Repo;
use App\Form\RepoFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\RepoRepository;
use App\Repository\UserRepository;
use App\Repository\GroupTRepository;
use App\Service\GitServiceFactory;
use App\DataObject\DataObjectFactory;
use Knp\Component\Pager\PaginatorInterface;


class RepoController extends AbstractController
{
    private $gitservice;
    private $repoRepository;
    private $userRepository;
    private $groupTRepository;
    private $appdataobjectFactory;
    private $paginator;

    public function __construct(GitServiceFactory $gitService,GroupTRepository $groupTRepository,RepoRepository $repoRepository, DataObjectFactory $appdataobjectFactory, PaginatorInterface $paginator, UserRepository $userRepository){
        $this->gitservice = $gitService;
        $this->repoRepository = $repoRepository;
        $this->groupTRepository = $groupTRepository;
        $this->appdataobjectFactory = $appdataobjectFactory;
        $this->paginator = $paginator;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/repo", name="repo")
     * 
     */
    public function indexAction(Request $request)
    {
    
        $parametersToValidate = $request->query->all();
        $parametersToValidate=array_values($parametersToValidate);
        $name = $parametersToValidate[1];
        $user = $parametersToValidate[0];
        $repo = $this->repoRepository->findOneBy(['name' => $name]);
        $user = $this->userRepository->findOneBy(['Username' => $user]);
        $gitService = $this->gitservice->create($repo);
        return $this->render('repo/index.html.twig', array(
            'repo' => $repo,
            'git' => $gitService,
            'user'=>$user
        ));
    }
    
    /**
     * @Route("/repository/{id}/{repo}", name="repository")
     * 
     */
    public function searchResult(Request $request,$id,$repo)
    {
    
        $parametersToValidate = $request->query->all();
        $parametersToValidate=array_values($parametersToValidate);
        // $name = $parametersToValidate[1];
        // $username = $parametersToValidate[0];
        $user = $this->userRepository->findOneBy(['Username' => $id]);
        $repo = $this->repoRepository->findOneBy(['name' => $repo]);
        $me = $repo;
        $gitService = $this->gitservice->create($repo);
        return $this->render('searchpage/index.html.twig', array(
            'repo' => $repo,
            'git' => $gitService,
            'username' =>$id,
            'user' => $user,
        ));
    }
  /**
     * @Route("/repogt/{id}/{grp}/{reponame}", name="repogt")
     * 
     */
    public function Action(Request $request,$id,$grp,$reponame)
    {
    
        $parametersToValidate = $request->query->all();
        $parametersToValidate=array_values($parametersToValidate);
        //$name = $parametersToValidate[1];

        $repo = $this->repoRepository->findOneBy(['id' => $reponame]);
        $name = $repo->getName();
        $group4 = $this->groupTRepository->findOneBy(['id' => $grp]);
        $report = new Repo();
        $form = $this->createform(RepoFormType::class,$report);
         $form->handleRequest($request);
           if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->repositoryService->createGroupRepos($user,$group4,$report);

            if ($result) {
                return $this->redirect($this->generateUrl('repogt', array('id'=>$id,'grp'=>$grp,'group' => $group4->getName(), 'reponame' => $report->getName(),'git'=>$gitService)));
            }


        }
        $gitService = $this->gitservice->create($repo);
        return $this->render('group/groupbranch.html.twig', array(
            'repo' => $repo,
            'name' => $name,
            'group' => $id,
            'group4' => $group4,
            'grp' => $grp,
            'git' => $gitService,
            'repo_form' => $form->createView(),
        ));
    }


    /**
     * Displays contributors
     *
     * @Route("/contributors", name="repo_contributors")
     */
    public function contributorsAction(Request $request)
    {
	$parametersToValidate = $request->query->all();
        $parametersToValidate=array_values($parametersToValidate);
        $name = $parametersToValidate[1];
        $repo = $this->repoRepository->findOneBy(['name' => $name]);
        $username = $parametersToValidate[0];
  
        $user = $this->userRepository->findOneBy(['Username' => $username]);

        $gitService = $this->gitservice->create($repo);

        $repoObject = $this->appdataobjectFactory->create($repo);
        $totalContributors = count($repoObject->getContributors());
        $contributors = array_slice($repoObject->getContributors(), 0, 24);

        return $this->render('repo/contributors.html.twig', array(
            'position' => 0,
            'total_contributors' => $totalContributors,
            'repo' => $repoObject,
            'contributors' => $contributors,
            'git' => $gitService,
            'user'=> $user,
        ));
    }

    /**
     * Displays contributors
     *
     * @Route("/contributors", name="repo_contributors_ajax")
     * 
     */
    public function contributorsAjaxAction(Request $request, Repo $repo, $offset)
    {
        $gitService = $this->gitservice->create($repo);

        $repoObject = $this->get('app.data_object_factory')->create($repo);
        $totalContributors = count($repoObject->getContributors());
        $contributors = array_slice($repoObject->getContributors(), $offset, 24);

        return $this->render('repo/fragments/contributors.html.twig', array(
            'position' => $offset,
            'total_contributors' => $totalContributors,
            'repo' => $repoObject,
            'contributors' => $contributors,
            'git' => $gitService,
        ));

    }


    /**
     * Display given blob in given tree
     *
     * @Route("/blob", name="repo_blob_view")
     * 
     */
    public function blobAction(Request $request)
    {
	  $parametersToValidate = $request->query->all();
          $parametersToValidate=array_values($parametersToValidate);
          $name = $parametersToValidate[1];
          $tree = $parametersToValidate[2];
          $path = $parametersToValidate[3];

          $username = $parametersToValidate[0];
  
          $user = $this->userRepository->findOneBy(['Username' => $username]);
  
          $repo = $this->repoRepository->findOneBy(['name' => $name]);
        if (! $this->TreePathExists($repo, $tree, $path)) {
            return $this->redirect($this->generateUrl('repo', array('user' => $repo->getOwner()->getUsername(), 'repo' => $repo->getName())));
        }

        $file = basename($path);
        $path = dirname($path);

        $vars = $this->getRepoVars($repo, $tree, $path);
        $vars['file'] = $file;
        $vars['user'] = $user;

        return $this->render('repo/blob.html.twig', $vars);
    }

    protected function treePathExists(Repo $repo, $tree, $path = null)
    {
        $gitService = $this->gitservice->create($repo);

        try {
            $gitService->refToSha($tree);
            $gitService->getTreeFromBranchPath($tree, $path);
        } catch (ReferenceNotFoundException $e) {
            return false;
        }

        return true;
    }

    protected function getRepoVars(Repo $repo, $tree, $path)
    {
        $gitService = $this->gitservice->create($repo);

        $branch = $tree; // Or this could be a tag
        $commit = $gitService->fetchCommitFromRef($tree);
        $tree = $gitService->getTreeFromBranchPath($tree, $path);


        $crumbtrail = array();

        if (strlen($path) > 0 && $path[0] == "/") {
            $path = substr($path, 1);
        }
        if ($path == "") {
            $pathArray = array("");
        } else {
            $pathArray = explode("/", $path);
        }
        array_unshift($pathArray, "");

        $p = array();
        foreach ($pathArray as $element) {
            $p[] = $element;
            $thisPath = join('/', $p);
            if ($element == "") {
                $element = "Root";
            }
            $crumbtrail[] = array(
                'name' => $element,
                'href' => $this->generateUrl('repo_tree', array(
                    'user' => $repo->getUserId()->getUsername(),
                    'repo' => $repo->getName(),
                    'tree' => $branch,
                    'path' => $thisPath,
                )),
            );
        }

        return array(
            'repo' => $this->appdataobjectFactory->create($repo),
            'git' => $gitService,
            'tree' => $tree,
            'commit' => $commit,
            'branch' => $branch,
            'crumbtrail' => $crumbtrail,
            'path' => $path,
        );
    }


    /**
      * Display specific tree based on branch/ref.
      *
      * @Route("/tree/{user}/{repo}/{tree}", name="repo_tree")
      *
      * 
      */
      public function treeAction(Request $request,$user, $repo, $tree)
      {
          $parametersToValidate = $request->query->all();
          $parametersToValidate=array_values($parametersToValidate);
        //   $name = $parametersToValidate[1];
        //   $tree = $parametersToValidate[2];
          $path = '/';
          $repo = $this->repoRepository->findOneBy(['name' => $repo]);
        //   $username = $parametersToValidate[0];
  
          $user = $this->userRepository->findOneBy(['Username' => $user]);
          if (! $this->treePathExists($repo, $tree, $path)) {
              return $this->redirect($this->generateUrl('repo', array('user' => $repo->getUerId()->getUsername(), 'repo' => $repo->getName())));
          }
  
          $vars = $this->getRepoVars($repo, $tree, $path);

          $vars['user'] = $user;
  
          return $this->render('repo/tree.html.twig', $vars);
      }




    /**
     * @Route("/repos/{id?}",name="repos_page")
     */
    public function showRepo(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $repos = null;

        if($id)
        {
            $repos = $em->getRepository(Repo::Class)->findOneBy(['id' => $id]);
        }
        $gitService = $this->gitservice->create($repos);
        return $this->render('repo/index.html.twig',[
            'repo' => $repos,
            'git' => $gitService,
        ]);
    }

    /**
     * @Route("/repos/user/{id?}",name="repos_public")
     */
    public function showPublic(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $repos = null;

        $user = $em->getRepository(User::Class)->findOneBy(['Username' => $id]);
       // $repo = $user->getRepos();
        $uid = $user -> getId();
        $connection = $em->getConnection();
        $vis = 0;
        $allOurBlogPosts = $em->getRepository(Repo::Class)->findBy(['userId'=>$uid,'visibility'=> $vis]);

        $blogPosts = $this->paginator->paginate(
            $allOurBlogPosts, 
            $request->query->getInt('page',1),
            $request->query->getInt('limit', 4)
        );
        return $this->render('searchpage/repos.html.twig',array('username'=>$id,'user'=>$user,'appointments' => $blogPosts,));
   
    }

    /**
     * @Route("/overview/user/{id?}",name="reposhow_public")
     */
    public function showRepoPublic(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $repos = null;

        $user = $em->getRepository(User::Class)->findOneBy(['Username' => $id]);
       // $repo = $user->getRepos();
        $uid = $user -> getId();
        $connection = $em->getConnection();
        $vis =0;

        $allOurBlogPosts = $em->getRepository(Repo::Class)->findBy(['userId'=>$uid,'visibility'=> $vis]);

        $blogPosts = $this->paginator->paginate(
            $allOurBlogPosts, 
            $request->query->getInt('page',1),
            $request->query->getInt('limit',4)
        );
        return $this->render('searchpage/repos2.html.twig',array('username'=>$id,'user'=>$user,'appointments' => $blogPosts,));
   
    }
    /**
      * Display specific tree based on branch/ref.
      *
      * @Route("/{user}/{repo}/{tree}/commits", name="commits_log")
      *
      * 
      */
      public function commitAction(Request $request, $user, $repo, $tree)
      {
          $parametersToValidate = $request->query->all();
          $parametersToValidate=array_values($parametersToValidate);
        //   $name = $parametersToValidate[1];
        //   $tree = $parametersToValidate[2];
          $path = '/';
          $repo = $this->repoRepository->findOneBy(['name' => $repo]);
          $gitService = $this->gitservice->create($repo);

        //   $username = $parametersToValidate[0];
  
          $user = $this->userRepository->findOneBy(['Username' => $user]);

          $repoObject = $this->appdataobjectFactory->create($repo);
          $commits = $repoObject->getCommitters($tree);
        
  
          if (! $this->treePathExists($repo, $tree, $path)) {
              return $this->redirect($this->generateUrl('repo', array('user' => $repo->getUerId()->getUsername(), 'repo' => $repo->getName())));
          }
  
          $vars = $this->getRepoVars($repo, $tree, $path);

          $categorized = array();

          $blogPosts = $this->paginator->paginate(
            $commits, 
            $request->query->getInt('page',1),
            $request->query->getInt('limit', 4)
        );

          $vars['commits'] = $blogPosts;
          $vars['user'] = $user;
  
          return $this->render('repo/commit.html.twig',$vars);
      }

    /**
     * @Route("/{user}/{repo}/{tree}/graph", name="tree_graph")
     */
    public function TreeGraphAction(Request $request,$user,$repo, $tree)
    {

        $parametersToValidate = $request->query->all();
          $parametersToValidate=array_values($parametersToValidate);
        //   $name = $parametersToValidate[1];
        //   $tree = $parametersToValidate[2];

          $path = '/';
          $repo = $this->repoRepository->findOneBy(['name' => $repo]);
          $gitService = $this->gitservice->create($repo);

        //   $username = $parametersToValidate[0];
  
          $user = $this->userRepository->findOneBy(['Username' => $user]);

          $repoObject = $this->appdataobjectFactory->create($repo);
          $graphItems = $repoObject->getGraph();

          if (! $this->treePathExists($repo, $tree, $path)) {
            return $this->redirect($this->generateUrl('repo', array('user' => $repo->getUerId()->getUsername(), 'repo' => $repo->getName())));
        }

        $vars = $this->getRepoVars($repo, $tree, $path);

        $vars['graphItems'] = $graphItems;
        $vars['user'] = $user;

        return $this->render('tree_graph/index.html.twig',$vars);
    }

     /**
      * Display specific tree based on branch/ref.
      *
      * @Route("/repo/{user}/{repo}/{tree}/{commit}", name="commits_view")
      *
      * 
      */
      public function commitViewAction(Request $request,$user,$repo,$tree,$commit)
      {
          $parametersToValidate = $request->query->all();
          $parametersToValidate=array_values($parametersToValidate);
        //   $name = $parametersToValidate[0];
        //   $hash = $parametersToValidate[1];
        //   $tree = $parametersToValidate[2];
          $path = '/';

        //   $username = $parametersToValidate[4];
  
          $user = $this->userRepository->findOneBy(['Username' => $user]);

          $repo = $this->repoRepository->findOneBy(['name' => $repo]);
          $gitService = $this->gitservice->create($repo);

          if (! $this->treePathExists($repo, $tree, $path)) {
            return $this->redirect($this->generateUrl('repo', array('user' => $repo->getUerId()->getUsername(), 'repo' => $repo->getName())));
        }

        $vars = $this->getRepoVars($repo, $tree, $path);

          $repoObject = $this->appdataobjectFactory->create($repo);
          $commits = $repoObject->getCommit($commit);
            
          $vars['commit'] = $commits;
          $vars['user'] = $user;

          return $this->render('repo/commit_view.html.twig',$vars);
      }

    /**
      * 
      *
      * @Route("/stats/{repo}/{branc}/{user}", name="stats")
      *
      * 
      */
      public function commitList(Request $request,$repo,$branc,$user)
      {
          $parametersToValidate = $request->query->all();
          $parametersToValidate=array_values($parametersToValidate);
          $name = $repo;
          $branch = $branc;
          $path = '/';
          $repo = $this->repoRepository->findOneBy(['name' => $name]);
          $gitService = $this->gitservice->create($repo);

          $username = $user;
  
          $user = $this->userRepository->findOneBy(['Username' => $username]);

          
          if (! $this->treePathExists($repo, $branch, $path)) {
            return $this->redirect($this->generateUrl('repo', array('user' => $repo->getUerId()->getUsername(), 'repo' => $repo->getName())));
        }

        $vars = $this->getRepoVars($repo, $branch, $path);
          $repoObject = $this->appdataobjectFactory->create($repo);
          $stats = $repoObject->getStatistics($branch);
          $authors = $repoObject->getAuthorStatistics($branch);
        
          $vars['stats'] = $stats;
          $vars['authors'] = $authors;
          $vars['user'] = $user;
  
          return $this->render('repo/stats.html.twig',$vars);
      }

}

