<?php


namespace AppBundle\Controller;


use BackendBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\RegisterType;
use AppBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    private $session;
    public function __construct(){
        $this->session = new Session();
    }
    public function loginAction(Request $request) {

        if (is_object($this->getUser())){
            return $this->redirect(('home'));
        }
//        This is a service in Suymfony to authenticate
        $authenticationUtils = $this->get("security.authentication_utils");
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();



        return $this->render(
            'user/login.html.twig',
            array(
                "titulo" => 'Login',
                "last_username" => $lastUsername,
                "error" => $error
            )
        );
    }

    public function registerAction (Request $request) {
        if (is_object($this->getUser())){
            return $this->redirect(('home'));
        }

        $user = new User();
        $form = $this -> createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted()){
            if($form->isValid()){
                $em = $this->getDoctrine()->getManager();
                /*$user_repo = $em->getRepository("BackendBundle:User");*/
                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                    ->setParameter('email', $form->get("email")->getData())
                    ->setParameter('nick', $form->get("nick")->getData());
                $user_isset = $query->getResult();

                if (count($user_isset) == 0){
                    $factory = $this->get("security.encoder_factory");
                    $encoder = $factory->getEncoder($user);

                    $password = $encoder->encodePassword($form->get("password")->getData(), $user->getSalt());

                    $user->setPassword($password);
                    $user->setRole("ROLE_USER");
                    $user->setImage(null);

                    /*Mandamos todos los objetos a Doctrine para posteriormente en Doctrine
                    De esta forma podemos seguir trabajando y guardarlo en otro momento
                    */
                    $em->persist($user);
                    /*flush pasa todos los objetos que tenemos persistiendo a la BD*/
                    $flush = $em->flush();

                    if ($flush == null){
                        $status = "El usuario se ha registrado correctamente";
                        $this->session->getFlashBag()->add("status", $status);
                        return $this->redirect('login');
                    }
                }else{
                    $status = "Este usuario ya existe";
                }
                $status = "Todo OK";
            }else{
                $status = "Algunos datos son incorrectos.";
            }
        }

        return $this->render("user/register.html.twig" , array(
           "form" => $form->createView()
        ));
    }

    public function nickTestAction(Request $request) {
        $nick = $request->get("nick");
        $em = $this->getDoctrine()->getManager();
        $user_repo = $em->getRepository("BackendBundle:User");

        $user_isset = $user_repo->findOneBy(array("nick" => $nick));

        if (count($user_isset) >= 1 && is_object($user_isset)){
            $result = "used";
        }else{
            $result = "unused";
        }

        return new Response($result);

    }

    public function editUserAction(Request $request) {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        // Bindeamos la request
        $form->handleRequest($request);
        // Preguntamos si el formulario ha sido Enviado
        if($form->isSubmitted()){
            // Preguntamos si el formulario es Válido
            if($form->isValid()){
                // Cargamos el Entity Manager para poder trabajar con el
                $em = $this->getDoctrine()->getManager();
                // Hacemos la consulta a BD con el createQuery del Entity Manager
                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                    ->setParameter('email', $form->get("email")->getData())
                    ->setParameter('nick', $form->get("nick")->getData());
                $user_isset = $query->getResult();
                $user_image = $user->getImage();
                // si el usuario logueado es igual al usuario que tenemos de la base de datos. 
                if (($user->getEmail() == $user_isset[0]->getEmail() && $user->getNick() == $user_isset[0]->getNick()) || count($user_isset) == 0){
                    // Capturando el Fichero que estamos subiendo
                    $file = $form["image"]->getData();
                    if(!empty($file) && $file!=null){
                        $ext = 'jpg';
                        // print_r($ext);
                        // die();
                        if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif'){
                            $file_name = $user->getId().time().".".$ext;
                            $file->move("uploads/users", $file_name);
                            $user->setImage($file_name);
                        }
                    }else{
                        $user->setImage($user_image);
                    }
                    /*Mandamos todos los objetos a Doctrine para posteriormente en Doctrine
                    De esta forma podemos seguir trabajando y guardarlo en otro momento
                    */
                    $em->persist($user);
                    /*flush pasa todos los objetos que tenemos persistiendo a la BD*/
                    $flush = $em->flush();

                    if ($flush == null){
                        $status = "Tus datos han sido guardados";
                    }
                }else{
                    $status = "Ha habido problemas. Corrige los datos.";
                }
                $status = "Todo Perfecto.";
            }else{
                $status = "Algunos datos son incorrectos.";
            }
            $this->session->getFlashBag()->add("status", $status);
            return $this->redirect('/');    
        }
        return $this->render("user/edit_user.html.twig", array(
            "form" => $form->createView()
        ));

    }

    public function usersAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT u FROM BackendBundle:User u ORDER BY u.id";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('user/users.html.twig', array(
            'pagination'=>$pagination,
            'title' => 'Usuarios'
        ));
    }
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $search = $request->query->get("search", null);

        if($search == null){
            return $this->redirect($this->generateURL("home_publications"));
        }
        else{

        }
        $dql = "SELECT u FROM BackendBundle:User u WHERE u.name LIKE :search OR u.surname LIKE :search OR u.nick LIKE :search ORDER BY u.id ASC";
        $query = $em->createQuery($dql)->setParameter('search', "%$search%");

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('user/users.html.twig', array(
            'pagination'=>$pagination,
            'title' => 'Usuarios'
        ));
    }

}