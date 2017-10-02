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
        echo "Follow Action";
        die();
    }
    

}