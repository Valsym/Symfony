<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('base_new.html.twig');
    }
}
//use Symfony\Component\HttpFoundation\Response;
//
//class DefaultController
//{
//    #[Route('/', name: 'app_home')]
//    public function hello(): Response
//    {
//        return new Response('Привет, Symfony!');
//    }
//}
