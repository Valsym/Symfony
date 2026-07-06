<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    public function hello(): Response
    {
        return new Response('Привет, Symfony!');
    }
}
