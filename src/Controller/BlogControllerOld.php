<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class BlogControllerOld
{
    public function show(int $id): Response
    {
        return new Response("Статья №{$id}");
    }
}
