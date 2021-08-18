<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RepoFormType;
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
use App\Entity\Messageholder;
use App\Entity\GroupT;
use App\Entity\repo;
use Knp\Component\Pager\PaginatorInterface;

class SecurityController extends AbstractController
{
    private $repositoryService;
    private $paginator;

    public function __construct(RepoService $repositoryService, PaginatorInterface $paginator){
        $this->repositoryService = $repositoryService;
        $this->paginator = $paginator;
    }
    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->isGranted('ROLE_USER')){
             return $this->RedirectToRoute('home');
         }
        if($this->isGranted('ROLE_ADMIN')){
             return $this->RedirectToRoute('profile');
         }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout",name="logout")
     */

     public function logoutAction(Request $request){
         
        throw new \Exception('Will be intercepted before getting here');
     }

     /**
     * @Route("/forgotpass",name="forgotpass")
     */

    public function forgottenPassword(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        \Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response
    {
 
        if ($request->isMethod('POST')) {
 
            $email = $request->request->get('kind');
             if(!empty($email)){
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneBy(['Email' => $email]);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('verify', 'Email not found!');
                return $this->render('bucket/forgotpass.html.twig');
            }
            $token = $tokenGenerator->generateToken();
 
            try{
                $user->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('verify','');
                return $this->render('bucket/forgotpass.html.twig');
            }
 
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);
 
            $message = (new \Swift_Message('Password Reset Link For UDOM BUCKET'))
                ->setFrom('info.ubucket@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "Please Click On This link : " . $url." to reset your password.",
                    'text/html'
                );
 
            $mailer->send($message);
 
            return $this->redirectToRoute('app_login');
             }
             else{
                $this->addFlash('verify', 'This field cannot be empty');
                return $this->render('bucket/forgotpass.html.twig');  
             }
        }
  
        return $this->render('bucket/forgotpass.html.twig');
     }


    /**
     * @Route("/reset_password/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
 
        if ($request->isMethod('POST')) {
           $newpass=$request->request->get('newpass');
           $conf=$request->request->get('confirm');
           if(!empty($newpass) || !empty($conf)){
            if(strlen($newpass)>7){
                if($newpass==$conf) {
            $entityManager = $this->getDoctrine()->getManager();
 
            $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('dangerToken', 'Token Incorrect');
                return $this->render('security/reset_password.html.twig', ['token' => $token]);
            }
 
            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $conf));
            $entityManager->flush();
 
         
            return $this->redirectToRoute('app_login');
        }
        else{
            $this->addFlash('Token',  'Password mismatch!');
            return $this->render('security/reset_password.html.twig', ['token' => $token]);   
        }
}
    else{
        $this->addFlash('Token', 'Password must be at least 8 characters!');
    return $this->render('security/reset_password.html.twig', ['token' => $token]);
       }
}
           else{
    $this->addFlash('dangerToken',  'All fields are required!');
    return $this->render('security/reset_password.html.twig', ['token' => $token]); 
              }
}   else {
 
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
 
}

     /**
        * @Route("/home/updpass",name="app_update_pass")
        *
        */
public function updpass(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
{
 $oldpass=strip_tags($request->request->get('oldpass'));
       $user=$this->getUser();
       $encold= $passwordEncoder->isPasswordValid($user,$oldpass);
       $newpass=strip_tags($request->request->get('newpass'));
       $confirm=strip_tags($request->request->get('conf'));
          if($request->isMethod('POST') && !empty($oldpass) && !empty($newpass) && !empty($confirm))
            {
               if($encold==true)
                  {
                      if(strlen($newpass)>7)
                        {
                             if($newpass==$confirm)
                                 {
                                    $conf=$passwordEncoder->encodePassword($user,$confirm);
                                    $user->setPassword($conf);
                                    $entityManager = $this->getDoctrine()->getManager();
                                    $entityManager->persist($user);
                                    $entityManager->flush();
                                     $session = $this->get('session');
                                     $session = new Session();
                                     $session->invalidate();
                                     return $this->redirect($this->generateUrl('app_login'));
                                 }
                             else
                                 {
                                      $this->addFlash('mismatch',  'Password mismatch!');
                                      return $this->render('bucket/changePassword.html.twig');
                                  }
                        }
                      else
                        {

                                    return $this->render('bucket/changePassword.html.twig');
                         }
                  }
                  else
                  {
                        $this->addFlash('current','The current password is incorrect');
                        return $this->render('bucket/changePassword.html.twig');
                  }

            }
          else
           {

                  return $this->render('bucket/changePassword.html.twig');
          }
        return $this->render('bucket/changePassword.html.twig');

}

  /**
     * @Route("/{id}/edit/name",name="edit")
     */
    public function edit(Request $request,$id){
        $user3 = $this->getUser();
        $user= $this->getUser()->getUsername();
        $group3 = $user3->getGroupTs();
        $group=new GroupT();
        $group4 =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id' => $id]);
        $group_name=$group4->getName();
        $form = $this->createFormBuilder($group4)

        ->add('name',TextType::class, array('label'=>'Group Name','attr' => array('class' => 'mdc-text-field mdc-text-field--box mdc-text-field--with-leading-icon w-80')))
        ->add('save',SubmitType::class, array(
            'label' => 'Save',
            'attr' => array('class' => 'btn btn-success btn-md')
        ))
        ->getForm();

        $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        return $this->redirectToRoute('grpavailable');
        }
        return $this->render('group/edit.html.twig',array('form'=>$form->createView()

                ));
    }

    /**
     * @Route("/group/create",name="create")
     */
     public function ugroup(Request $request): Response
    {
     $user=$this->getUser();
       if($user != null){
       $username=$user->getUsername();
       $joining =$this->getDoctrine()->getRepository(GroupT::class)->findAll();
       $gname=$request->request->get('gname');
       if ($request->isMethod('POST'))
       {
        if(!empty($gname))
        {
            $group = new GroupT();
            $user1 =$this->getDoctrine()->getRepository(User::class)->findOneBy(['Username' => $username]);
            $group->setName($gname);
            $group->setOwner($user->getUsername());
            $group->addUserToGroup($user1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute('grpavailable');
          }
         else
         {
            $this->addFlash('name',  'Please enter group name');
            return $this->render('group/group.html.twig',['group'=>$joining]);
         }
        }
            return $this->render('group/group.html.twig',['group'=>$joining]);
        }
       else{
            return $this->redirectToRoute('app_login');
       }
    }
 /**
     * @Route("/pathcontent/{grp}/{id}",name="content")
     */

 public function pathcontent(Request $request,$id,$grp){
     $user=$this->getUser();
     if($user != null){
        $joining =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id'=>$grp]);
        $report = new Repo();
        $form = $this->createform(RepoFormType::class,$report);
        $em=$this->getDoctrine()->getManager();
        $connection=$em->getConnection();
        $statement13=$connection->prepare("select user.username,user.userimage from user inner join group_t_user on user.id=group_t_user.user_id where group_t_user.group_t_id=:sub");
        $statement13->bindParam(':sub',$grp);
        $statement13->execute();
        $member=$statement13->fetchAll();
        $statement12=$connection->prepare("SELECT count(*) from repo inner join group_t on repo.group_id_id=group_t.id where group_t.id=:sub");
        $statement12->bindParam(':sub',$grp);
        $statement12->execute();
        $statement121=$connection->prepare("SELECT msg,username,messagetimes as times FROM `user`
        inner join messageholder on user.id=messageholder.sent_by_id
        inner join group_t on messageholder.groupmessage_id=group_t.id where group_t.id=:sub order by messageholder.id");
        $statement121->bindParam(':sub',$grp);
        $statement121->execute();
        $today= new \DateTime('now');
        $today = $today->format('H:i');
        $username=$user->getUsername();
        $repo=$statement12->fetchOne();
        $name = $joining->getOwner();
        $group = $joining->getName();
        $oldchat =$statement121->fetchAll();
        $addmember=strip_tags($request->request->get('member'));
          $form->handleRequest($request);
           if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->repositoryService->createGroupRepos($user,$joining,$report);

            if ($result) {
                  return $this->redirect($this->generateUrl('repogt', array('id'=>$id,'grp'=>$grp,'group' => $joining->getName(), 'reponame' => $report->getId(),)));
            }


        }

    if ($request->isMethod('POST') && !empty($addmember) )
    {
        $user2 =$this->getDoctrine()->getRepository(User::class)->findOneBy(['Username' => $addmember]);
        if($user2==true)
        {
            $entityManager = $this->getDoctrine()->getManager();
            $user3 =$user2->getId();
            $statement=$connection->prepare("select user_id from group_t_user where group_t_user.user_id = '".$user3."' and group_t_user.group_t_id ='".$grp."'");
            $statement->execute();
            $verify=$statement->fetchAll();
             if(!$verify){
            $joining->addUser($user2);
            $entityManager->persist($joining);
            $entityManager->flush();
            return $this->redirectToRoute('content',['id'=>$id,'grp'=>$grp]);
            }
            else{
                     $this->addFlash('success','This username  '.' '.$addmember.'  already added to this group!');
                    return $this->render('group/content.html.twig',['today'=>$today,'oldchat'=>$oldchat,'username'=>$username,'group'=>$group,'name'=>$name,'member'=>$member,'grp'=>$grp,'repo'=>$repo, 'repo_form' => $form->createView()]);
            }
        }
        else
        {
            $this->addFlash('success','username  '.$addmember. ' not found!');
            return $this->render('group/content.html.twig',['today'=>$today,'oldchat'=>$oldchat,'username'=>$username,'group'=>$group,'name'=>$name,'member'=>$member,'repo'=>$repo,'grp'=>$grp ,'repo_form' => $form->createView()]);
        }

        }
         return $this->render('group/content.html.twig',['today'=>$today,'oldchat'=>$oldchat,'username'=>$username,'group'=>$group,'name'=>$name,'member'=>$member,'repo'=>$repo,'grp'=>$grp,  'repo_form' => $form->createView()]);
        }
       else{
        return $this->redirectToRoute('app_login');
       }
 }
   /**
     * @Route("/pathcontent/{id}/{grp}/grprepos",name="grprepos")
     */
    public function grprepos(Request $request,$id,$grp){
        $user = $this->getUser();
        $user1 = $this->getUser()->getUsername();
        $group4 =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id' => $grp]);
        $report = new Repo();
        $form = $this->createform(RepoFormType::class,$report);
         $form->handleRequest($request);
           if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->repositoryService->createGroupRepos($user,$group4,$report);

            if ($result) {
                  return $this->redirect($this->generateUrl('repogt', array('id'=>$id,'grp'=>$grp,'group' => $group4->getName(), 'reponame' => $report->getId(),)));
            }


        }
        $test=$group4->getOwner();
        return $this->render('group/grouprepository.html.twig',array(
            'repo_form' => $form->createView(),
            'user'=> $user1,
            'group4' => $group4,
            'test' => $test,
            'grp' => $grp,
            'group'=> $id

        ));;
       }

    
   /**
     * @Route("/grpavailable",name="grpavailable")
     */
    public function grpavailable(Request $request){
        $user = $this->getUser();
        $id = $user->getId();
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $sql = $connection->prepare("SELECT group_t.id,group_t.name from group_t_user inner join group_t on group_t.id = group_t_user.group_t_id  where user_id ='".$id."' ");
        $sql->execute();
        $group = $sql->fetchAll();
          $group= $this->paginator->paginate(
            $group,
            $request->query->getInt('page',1),
            $request->query->getInt('limit',6)
        );
        $user3 = $this->getUser()->getUsername();
        $success='';
        return $this->render('group/group.html.twig',array('success'=>$success,
        'user' => $user,
        'user3'=>$user3,
        'group' => $group
         ));
       }

/**
     * @Route("/Member/{groupId}/{grp}/remove/{userId}",name="removemember")
     * 
     */
    public function remove($groupId,$userId,$grp){
        $success="";
        $user = $this->getUser()->getUsername();
        /**
         *  @var User
         */
        $user_id =$this->getDoctrine()->getRepository(User::class)->findOneBy(['Username'=>$userId]);
        $group_id =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id'=>$grp]);
        $em = $this->getDoctrine()->getManager();
        $group_id->removeUserToGroup($user_id);
        $em->persist($group_id);
        $em->flush();
        return $this->redirectToRoute('content',['grp'=>$grp,'id'=>$group_id->getName()]);
    }
 /**
     * @Route("/{grp}/removed/delete",name="removeemptygroup")
     * 
     */
    public function deleteEmpty(Request $request,$grp){
        $user = $this->getUser();
        $group_id =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id'=>$grp]);
        $message=$this->getDoctrine()->getRepository(Messageholder::class)->findOneBy(['groupmessage'=>$grp]);
        $group_name=$group_id->getName();
        $test = $group_id->getOwner();

        $em = $this->getDoctrine()->getManager();
        if($message){
        $group_id->setGroupmessage(null);
        }
        $em->remove($group_id);
        $em->flush();
        $title = $user->getTitle();
        if($title == 'administrator' or $title == 'superadmin'){
        return $this->redirect($this->generateUrl('groupt', array('group_name'=>$group_name.'..../Deleted')));
        }else{
        return $this->redirect($this->generateUrl('grpavailable', array('group_name'=>$group_name.'..../Deleted')));
        }
    }
     /**
     * @Route("/Repo/{Id}/set",name="private")
     * 
     */
    public function private(Request $request,$Id){
        $user = $this->getUser();
         if($user != null){
        $id = $user->getId();
        $repost =$this->getDoctrine()->getRepository(Repo::class)->findOneBy(['id'=>$Id]);
        $em = $this->getDoctrine()->getManager();
        $repost->setVisibility(true);
        $em->persist($repost);
        $em->flush();
        return $this->redirectToRoute('work');
        }
        else{
         return $this->redirectToRoute('app_login');
        }
    } 
    /**
     * @Route("/me/{Id}/public/set",name="public_repo")
     * 
     */
    public function public(Request $request,$Id){
        $user = $this->getUser();
        if($user != null){
        $user3 = $this->getUser()->getUsername();
        $id = $user->getId();
        $repost =$this->getDoctrine()->getRepository(Repo::class)->findOneBy(['id'=>$Id]);
        $em = $this->getDoctrine()->getManager();
        $repost->setVisibility(false);
        $em->persist($repost);
        $em->flush();
          return $this->redirectToRoute('work');
        }
        else{
         return $this->redirectToRoute('app_login');
        }
        
    } 
    /**
     * @Route("/Repo/{gid}/{Id}/{grp}/publish",name="publish")
     * 
     */
    public function publish(Request $request,$gid, $Id,$grp){
        $success="";
        $user = $this->getUser(); 
        $group_id =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id'=>$grp]);
        $repost =$this->getDoctrine()->getRepository(Repo::class)->findOneBy(['id'=>$Id]);
        $test = $group_id->getOwner();
        $em = $this->getDoctrine()->getManager();
        $group_id->removeRepo($repost);
        $repost->setUserId($user);
        $em->persist($group_id);
        $em->flush();
        $name=$repost->getName();
        return $this->render('group/showmessageafterpublish.html.twig',array(
            'user' => $user,
            'group4' => $group_id,
            'test' => $test,
            'group'=> $gid,
            'id' => $Id,
            'grp' => $grp,
            'name'=>$name,
            'repost'=>$repost,
            'success'=>$success,
            
            
        ));;
        
    } 
/**
     * @Route("/video/code/room/{grp}/{id}",name="video")
     */

 public function video(Request $request,$id,$grp){
     $user=$this->getUser();
     if($user != null){
        $joining =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id'=>$grp]);
        $report = new Repo();
        $form = $this->createform(RepoFormType::class,$report);
        $em=$this->getDoctrine()->getManager();
        $connection=$em->getConnection();
        $statement13=$connection->prepare("select user.username,user.userimage from user inner join group_t_user on user.id=group_t_user.user_id where group_t_user.group_t_id=:sub");
        $statement13->bindParam(':sub',$grp);
        $statement13->execute();
        $member=$statement13->fetchAll();
        $statement12=$connection->prepare("SELECT count(*) from repo inner join group_t on repo.group_id_id=group_t.id where group_t.id=:sub");
        $statement12->bindParam(':sub',$grp);
        $statement12->execute();
        $repo=$statement12->fetchOne();
        $name = $joining->getOwner();
        $group = $joining->getName();

          $form->handleRequest($request);
           if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->repositoryService->createGroupRepos($user,$joining,$report);

            if ($result) {
                  return $this->redirect($this->generateUrl('repogt', array('id'=>$id,'grp'=>$grp,'group' => $joining->getName(), 'reponame' => $report->getId(),)));
            }


        }

         return $this->render('group/videocode.html.twig',['group'=>$group,'member'=>$member,'name'=>$name,'repo'=>$repo,'grp'=>$grp,  'repo_form' => $form->createView()]);
        }
       else{
        return $this->redirectToRoute('app_login');
       }
 }

    
}


