<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RepoFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use App\Service\RepoService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\User;
use App\Entity\Repo;
use App\Repository\RepoRepository;
use Knp\Component\Pager\PaginatorInterface;

class UserController extends AbstractController
{
    private $repositoryService;
    private $repoRepository;
    private $paginator;

    public function __construct(RepoService $repositoryService,RepoRepository $repoRepository, PaginatorInterface $paginator){
        $this->repositoryService = $repositoryService;
        $this->repoRepository = $repoRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        $user = $this->getUser();
        $repo = $user->getRepos();
        return $this->render('user/yourwork.html.twig',array(
            'user' => $user,
            'repo' => $repo,
        ));;

    }
       /**
     * @Route("/handleSearch/{input?}",name="handle_search")
     */
    public function handleSearchRequest(Request $request, $input){
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $user = $this->getUser();
        $id = $user->getId();
        if($input)
        {
           // $data = $em->getRepository(Repo::Class)->findBy(['name' => $input, 'userId' => $id]);
          // $data = $this->repoRepository->findByName($input);
          $sql = $connection->prepare("SELECT * from repo where user_id_id = '".$id."' AND name LIKE '%".$input."%'");
          $sql->execute();
          $data = $sql->fetchAll();
        } else{
            //$data = $em->getRepository(Repo::Class)->findAll();
        }

        $normalizers = [
            New ObjectNormalizer()
        ];
        $encoders = [
            New JsonEncoder()
        ];

        $serializer = new Serializer($normalizers, $encoders);
      //  $data = $serializer->serialize($data, 'json');
      //  return new JsonResponse($data, 200, [], true);
    // Serialize your object in Json
    $jsonObject = $serializer->serialize($data, 'json', [
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        }
    ]);

     // For instance, return a Response with encoded Json
    return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
}

        /**
     * @Route("/Search/{input?}",name="search")
     */
    public function handleSearch(Request $request, $input){
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $vis = 0;
        if($input)
        {
           // $data = $em->getRepository(Repo::Class)->findBy(['name' => $input, 'userId' => $id]);
          // $data = $this->repoRepository->findBy( ['visibility'=>$vis]);
         // $sql = $connection->prepare("SELECT * from repo where visibility = '".$vis."' AND name LIKE '%".$input."%'");
         $sql = $connection->prepare("SELECT * from user where username LIKE '%".$input."%'");
          $sql->execute();
          $data = $sql->fetchAll();
        } else{
           // $data = $em->getRepository(Repo::Class)->findAll();
        }

        $normalizers = [
            New ObjectNormalizer()
        ];
        $encoders = [
            New JsonEncoder()
        ];

        $serializer = new Serializer($normalizers, $encoders);
      //  $data = $serializer->serialize($data, 'json');
      //  return new JsonResponse($data, 200, [], true);
    // Serialize your object in Json
    $jsonObject = $serializer->serialize($data, 'json', [
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        }
    ]);

     // For instance, return a Response with encoded Json
    return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
}

 /**
     * @Route("/repository", name="work")
     */
    public function work(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $id = $user->getId();
        $repo = new Repo();
        $form = $this->createform(RepoFormType::class,$repo);
        $form->handleRequest($request);

        $allOurBlogPosts = $em->getRepository(Repo::Class)->findBy(['userId'=>$id]);

        $blogPosts = $this->paginator->paginate(
            $allOurBlogPosts, 
            $request->query->getInt('page',1),
            $request->query->getInt('limit', 6)
        );


        if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->repositoryService->create($user, $repo);

            if ($result) {
                return $this->redirectToRoute('work');
            }

        }

        return $this->render('user/home.html.twig',array(
            'user' => $user,
            'appointments' => $blogPosts, 'repo_form' => $form->createView()
        ));;

    }

    
 }