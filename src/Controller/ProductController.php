<?php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
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

    #[Route('/test-products', name: 'test_products')]
    public function testProduct(ProductRepository $ProductRepository): Response
    {
        $key = 'fan';
        $products = $ProductRepository->findProductsByKey($key);

        // Выведем результат
        dd($products); // dump and die - покажет массив результатов поиска
    }

}
