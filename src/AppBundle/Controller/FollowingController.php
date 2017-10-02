<?php


namespace AppBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\HttpFoundation\Response;

use BackendBundle\Entity\User;
use BackendBundle\Entity\Following;

class FollowingController extends Controller
{
    private $session;
    public function __construct(){
        $this->session = new Session();
    }

    public function followAction() {
        $user = $this->getUser();
        $followedId = $request->get('followed');

        $em = $this->getDoctrine()->getManager();

        $user_repo = $em->getRepository('BackendBundle:User');

        $followed = $user_repo->fin($followed_id);
        $following = new Following();
        $following->setUser($user);
        $following->setFollowed($followed);

        $em->persist($following);
        $flush = $em->flush();

        if($flush == null){
            $status = "Ahora estás siguiendo a este usuario";
        }
        else{
            $status = "Ha habido un error. Prueba de nuevo más tarde";
        }

        return new Response($status);
    }
    

}