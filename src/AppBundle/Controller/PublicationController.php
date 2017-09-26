<?php
/**
 * Created by PhpStorm.
 * User: fabian
 * Date: 23/8/17
 * Time: 16:42
 */

namespace AppBundle\Controller;



use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PublicationController extends Controller
{
    public function indexAction(Request $request) {
        return $this->render('publication/home.html.twig');
    }
}