<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RepoFormType;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Service\RepoService;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;
use App\Entity\GroupT;
use App\Entity\repo;
use App\Entity\Messageholder;
use Knp\Component\Pager\PaginatorInterface;

class ProfileController extends AbstractController
{
    private $repositoryService;
    private $paginator;

    public function __construct(RepoService $repositoryService, PaginatorInterface $paginator){
        $this->repositoryService = $repositoryService;
        $this->paginator = $paginator;
    }
    /**
     * @Route("/profile", name="profile")
     */
     public function index(): Response
    {
     $user=$this->getUser();
       if($user != null){
       $joining =$this->getDoctrine()->getRepository(User::class)->findBy(['title'=>'user']);
       $privilege =$this->getDoctrine()->getRepository(User::class)->findBy(['title'=>'administrator']);
       $group =$this->getDoctrine()->getRepository(GroupT::class)->findAll();
       $publicrepo =$this->getDoctrine()->getRepository(Repo::class)->findBy(['visibility'=>'public']);
       $privaterepo =$this->getDoctrine()->getRepository(Repo::class)->findBy(['visibility'=>'private']);
        return $this->render('profile/index.html.twig', ['controller_name' => 'ProfileController',
       'join'=>$joining,'privilege'=>$privilege,'group'=>$group,'public'=>$publicrepo,'private'=>$privaterepo]);
        }
       else{
        return $this->redirectToRoute('app_login');
       }
    }
      /**
     * @Route("/profile/admin", name="admin")
     */
    public function uadmin(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
     $user=$this->getUser();
       if($user != null){
       $joining =$this->getDoctrine()->getRepository(User::class)->findBy(['title'=>'administrator']);
        $em = $this->getDoctrine()->getManager();
        $connection=$em->getConnection();
        $statement=$connection->prepare("select username from user where username='".$request->request->get('username')."'");
        $statement2=$connection->prepare("select email from user where email='".$request->request->get('email')."'");
        $statement->execute();
        $statement2->execute();
        $verify=$statement->fetchAll();
        $verifyemail=$statement2->fetchAll();
        $cpass="ubucket";
        if ($request->isMethod('POST')) {
            if(!$verify){
                if(!$verifyemail){
            $user = new User();
            $user->setFirstname($request->request->get('fname'));
            $user->setUsername($request->request->get('uname'));
            $user->setEmail($request->request->get('email'));
            $user->setTitle('administrator');
            $user->setRoles([
                            "ROLE_ADMIN"
                            ]);
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $cpass
            ));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("admin");
        }
        else
        {
            $this->addFlash('username',  'This email  '.$request->request->get('email').' already exists!');
            return $this->render('profile/adminusers.html.twig',['display'=>$joining]);
         }
        }
        else
        {
            $this->addFlash('username',  'This username '.$request->request->get('uname').' already exists!');
           return $this->render('profile/adminusers.html.twig',['display'=>$joining]);
        }

        }
        return $this->render('profile/adminusers.html.twig',['display'=>$joining]);
        }
       else{
        return $this->redirectToRoute('app_login');
       }
    }
     /**
     * @Route("/profile/joining/user/list", name="joining")
     */
    public function ujoining(): Response
    {
     $user=$this->getUser();
       if($user != null){
       $joining =$this->getDoctrine()->getRepository(User::class)->findBy(['title'=>'user']);
        return $this->render('profile/joinusers.html.twig',['display'=>$joining]);
        }
       else{
        return $this->redirectToRoute('app_login');
       }
    }

/**
     * @Route("/profile/remove/user", name="removeuser")
     */
    public function removeadmin(Request $request): Response
    {

            $user1=$this->getUser();
            $em=$this->getDoctrine()->getManager();
            $userid=$user1->getId();
            $parametersToValidate = $request->query->all();
            $parametersToValidate=array_values($parametersToValidate);
            $name = $parametersToValidate[0];
            $projectuser =$this->getDoctrine()->getRepository(User::class)->findOneBy(['Username' => $name]);
            $id=$projectuser->getId();
            $enter =$this->getDoctrine()->getRepository(Repo::class)->findOneBy(['userId' => $id]);
            $message =$this->getDoctrine()->getRepository(Messageholder::class)->findOneBy(['SentBy' => $id]);
            $enter1 =$this->getDoctrine()->getRepository(User::class)->findOneBy(['title' => 'superadmin']);

            if($projectuser){

                    $title = $projectuser->getTitle();
                    if($title == 'administrator' or $title == 'superadmin'){
                      if($enter){
                       $enter->setUserId($enter1);
                       $enter->setVisibility(true);
                       }
                       if($message)
                       {
                       $projectuser->removeUsersent($message);
                       }
                       $em->remove($projectuser);
                       $em->flush();
                       return $this->redirectToRoute('admin');
                    }
                    else{
                      if($enter){
                       $enter->setUserId($enter1);
                       $enter->setVisibility(true);
                       }
                      if($message)
                       {
                       $projectuser->removeUsersent($message);
                       }
                      $em->remove($projectuser);
                      $em->flush();
                      return $this->redirectToRoute('joining');
                    }

            }
            else if($user1){
                   if($enter){
                       $enter->setUserId($enter1);
                       $enter->setVisibility(true);
                     }
                      if($message)
                       {
                       $projectuser->removeUsersent($message);
                       }
                    $em->remove($projectuser);
                    $em->flush();
                    $success="Your account no longer exists contact administrator for more info";
                    $session = $this->get('session');
                    $session = new Session();
                    $session->invalidate();
                    return new response($success);

            }
            else{
                    return new response("failed to remove user");
                 }


    }
         /**
     * @Route("/profile/admin/revoke/privilege/manager", name="revoke")
     */
    public function revoke(Request $request): Response
    {
     $user=$this->getUser();
       if($user != null){
        $parametersToValidate = $request->query->all();
        $parametersToValidate=array_values($parametersToValidate);
        $name = $parametersToValidate[0];
        $joining =$this->getDoctrine()->getRepository(User::class)->findOneBy(['Username'=>$name]);
        $joining->setRoles(
                ['ROLE_USER']
        );
        $joining->setTitle('user');
        $em = $this->getDoctrine()->getManager();
        $em->persist($joining);
        $em->flush();
        return $this->redirectToRoute('admin');
        }
       else{
        return $this->redirectToRoute('app_login');
       }
    }
     /**
     * @Route("/profile/repository/available/list", name="repos")
     */
    public function urepo(Request $request): Response
    {
     $user=$this->getUser();
       if($user != null){
        $repo = new Repo();
        $form = $this->createform(RepoFormType::class,$repo);
        $form->handleRequest($request);
       $joining =$this->getDoctrine()->getRepository(Repo::class)->findAll();
        $em = $this->getDoctrine()->getManager();
        $connection=$em->getConnection();
        $statement2=$connection->prepare("
         SELECT repo.id,username FROM
          `user` inner join repo on repo.user_id_id=user.id");
        $statement2->execute();
        $verify=$statement2->fetchAll();
         if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->repositoryService->create($user, $repo);

            if ($result) {
                return $this->redirectToRoute('repos');
            }

        }
        return $this->render('profile/repositories.html.twig',['display'=>$joining,
        'verify'=>$verify,'user'=>$user,'repo_form' => $form->createView()]);
        }
       else{
        return $this->redirectToRoute('app_login');
       }
    }
          /**
     * @Route("/profile/registered/group/available", name="groupt")
     */
    public function ugroup(Request $request): Response
    {
     $user1=$this->getUser();
       if($user1 != null){
        $id=$user1->getId();
        $em = $this->getDoctrine()->getManager();
        $join = $em->getRepository(GroupT::class)->findAll();
        $connection=$em->getConnection();
        $statement=$connection->prepare("
         SELECT DISTINCT group_t.id,owner,name FROM `user` inner join
         group_t_user on user.id=group_t_user.user_id
         inner join group_t on group_t_user.group_t_id=group_t.id where group_t_id not in (SELECT group_t.id FROM `user` inner join
         group_t_user on user.id=group_t_user.user_id
         inner join group_t on group_t_user.group_t_id=group_t.id
          where user.id='".$id."') ");
        $statement->execute();
        $joining=$statement->fetchAll();
        $statement2=$connection->prepare("
         SELECT DISTINCT group_t.id,owner,name FROM `user` inner join
         group_t_user on user.id=group_t_user.user_id
         inner join group_t on group_t_user.group_t_id=group_t.id where group_t_id in (SELECT group_t.id FROM `user` inner join
         group_t_user on user.id=group_t_user.user_id
         inner join group_t on group_t_user.group_t_id=group_t.id
          where user.id='".$id."') ");
        $statement2->execute();
       $user=$statement2->fetchAll();
       $gname=$request->request->get('gname');
       if ($request->isMethod('POST'))
       {
        if(!empty($gname))
        {
            $group = new GroupT();
            $user1 =$this->getDoctrine()->getRepository(User::class)->findOneBy(['Username' => $user1->getUsername()]);
            $group->setName($gname);
            $group->setOwner($user1->getUsername());
            $group->addUser($user1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute("groupt");
          }
         else
         {
            $this->addFlash('name',  'Please enter group name');
            return $this->render('profile/group.html.twig',['display'=>$joining,'verify'=>$user,'join'=>$join]);
         }
        }
            return $this->render('profile/group.html.twig',['display'=>$joining,'verify'=>$user,'join'=>$join]);
        }
       else{
            return $this->redirectToRoute('app_login');
       }
    }

     /**
     * @Route("/repository/handleSearch/{name}/{input?}",name="public_search")
     */
    public function repoSearchRequest(Request $request, $input, $name){
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $test = $em->getRepository(User::Class)->findOneBy(['Username'=>$name]);
            if($input)
            {
              $id = $test->getId();
              $sql = $connection->prepare("SELECT * from repo where user_id_id = '".$id."' AND name LIKE '%$input%'");
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
}