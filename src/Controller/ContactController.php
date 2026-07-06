<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class ContactController
{
    public function show(): Response
    {
        return new Response('Наши контакты: example@mail.ru');
    }
}
