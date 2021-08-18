<?php
namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Security\FormLoginAuthenticator;
use App\Entity\User;
use App\Entity\GroupT;
use App\Entity\Messageholder;
use App\Form\UserType;




class RegistrationController extends AbstractController
{



    /**
     * @Route("/registration", name="registration")
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder,GuardAuthenticatorHandler $guardHandler, FormLoginAuthenticator $formAuthenticator)
    {
        $em = $this->getDoctrine()->getManager();
        $connection=$em->getConnection();
        $statement=$connection->prepare("select username from user where username = '".$request->request->get('uname')."' ");
        $statement2=$connection->prepare("select email from user where email = '".$request->request->get('email')."' ");
        $statement->execute();
        $statement2->execute();
        $verify=$statement->fetchAll();
        $verifyemail=$statement2->fetchAll();
        if ($request->isMethod('POST')) {
            if(!$verify){
                if(!$verifyemail){
            $user = new User();
            $user->setFirstname($request->request->get('fname'));
            $user->setUsername($request->request->get('uname'));
            $user->setEmail($request->request->get('email'));
             $user->setTitle('user');
            $user->setRoles([
                            "ROLE_USER"
                            ]);
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $request->request->get('cpass')
            ));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("app_login");
        }
        else{
            $this->addFlash('danger',  'This email  '.$request->request->get('email').' already used!');
    return $this->render('registration/register.html.twig'); 
        }
    }
        else{
            $this->addFlash('danger',  'This username '.$request->request->get('user').' already exists!');
    return $this->render('registration/register.html.twig'); 
        }
    
        }

        return $this->render('registration/register.html.twig');
    }
    /**
     * @Route("/message/pass/{grp}", name="message")
     */
    public function message(Request $request,$grp): Response
    {
     $user=$this->getUser();
       if($user != null){
       $enter = new Messageholder();
       $userid=$user->getId();
       $joining =$this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=>$userid]);
       $group =$this->getDoctrine()->getRepository(GroupT::class)->findOneBy(['id'=>$grp]);
       if($group){
       $enter->setMsg($request->request->get('lafesa'));
       $enter->setGroupmessage($group);
       $today=new \DateTime('now');
       $today=$today->format('H:i');
       $enter->setMessagetimes($today);
       $enter->setSentBy($joining);
       $em = $this->getDoctrine()->getManager();
       $em->persist($enter);
       $em->flush();
       return $this->redirectToRoute('content',['grp'=>$grp,'id'=>$group->getName()]);
       }

        }
       else{
        return $this->redirectToRoute('app_login');
       }
    }

}
