<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends AbstractController
{
    public function show(string $slug): Response
    {
        return new Response("Наш продукт: {$slug}");
    }

    public function apiData(): JsonResponse
    {
        $data = [
            'name' => 'Maks',
            'course' => 'Symfony for beginners',
        ];

        return $this->json($data);
    }

}
