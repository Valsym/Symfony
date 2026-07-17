<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Products')]
final class ApiProductController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Возвращает список товаров'
    )]
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function index(ProductRepository $repository): JsonResponse
    {
        $products = $repository->findAll();
        return $this->json($products);
    }

    #[OA\Response(
        response: 200,
        description: 'Возвращает товар по ID'
    )]
    #[OA\Response(
        response: 404,
        description: 'Товар не найден'
    )]
    #[Route('/api/products/{id}', name: 'api_product_show', methods: ['GET'])]
    public function show(Product $product, SerializerInterface $serializer): Response
    {
        $data = $serializer->serialize($product, 'json', [
            'datetime_format' => 'Y-m-d H:i:s',
        ]);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }
//    public function show(Product $product): JsonResponse
//    {
//        return $this->json($product);
//    }

    #[Route('/api/products', name: 'api_product_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);

        $em->persist($product);
        $em->flush();

        return $this->json($product, 201);
    }

    #[Route('/api/products/{id}', name: 'api_product_update', methods: ['PUT'])]
    public function update(Product $product, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product->setName($data['name'] ?? $product->getName());
        $product->setPrice($data['price'] ?? $product->getPrice());

        $em->flush();

        return $this->json($product);
    }

    #[Route('/api/products/{id}', name: 'api_product_delete', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return $this->json(null, 204);
    }
}
