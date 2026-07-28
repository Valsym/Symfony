<?php
namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route;

class HelloController extends AbstractController
{
    #[Route('/hello', name: 'app_hello')]
    public function index(): Response
    {
        return $this->render('hello/index.html.twig', [
            'message' => 'Привет из шаблона Twig!'
        ]);

//        return new Response('Привет, это мой первый контроллер!');
    }

    #[Route('/test-profiler-join', name: 'app_test_profiler_join', methods: ['GET'])]
    public function testProfilerWithJoin(EntityManagerInterface $entityManager): Response
    {
        // РЕШЕНИЕ N+1 ПРОБЛЕМЫ:
        // Используем LEFT JOIN для загрузки категорий вместе с продуктами
        // Вместо 51 запроса (1 + 50) теперь всего 1
        $products = $entityManager->createQueryBuilder()
            ->select('p', 'c')
            ->from(Product::class, 'p')
            ->leftJoin('p.category', 'c')
            ->getQuery()
            ->getResult();





        //$products = $entityManager->getRepository(Product::class)->findAll();
        $result = '';

        foreach ($products as $product) {
            $category = $product->getCategory();
            //$categoryCount = count($product->getCategory()); // Запрос выполняется здесь! (N+1 проблема)
            $result .= "Product {$product->getId()}: {$product->getName()} - Categories: {$category->getName()}\n<br>";
        }

        // Возвращаем HTML (не plain text)
        return new Response(
            '<html><body><h1>Test N+1 Problem</h1><pre>' . $result . '</pre></body></html>'
        );
    }

    #[Route('/test-n-plus-one-force', name: 'app_test_n_plus_one_force')]
    public function testNPlusOneForce(EntityManagerInterface $entityManager): Response
    {
        $entityManager->clear();

        // Используем нативный SQL, чтобы Doctrine не кешировала
        $connection = $entityManager->getConnection();
        $products = $connection->fetchAllAssociative('SELECT * FROM product');

        $result = '';
        foreach ($products as $productData) {
            // Принудительно загружаем каждую категорию отдельным запросом
            if ($productData['category_id']) {
                $category = $connection->fetchAssociative(
                    'SELECT * FROM category WHERE id = ?',
                    [$productData['category_id']]
                );
                $categoryName = $category ? $category['name'] : 'No name';
            } else {
                $categoryName = 'No category';
            }

            $result .= "Product {$productData['id']}: {$productData['name']} - Category: {$categoryName}\n<br>";
        }

        return new Response('<html><body><h1>Real N+1 Test</h1><pre>' . $result . '</pre></body></html>');
    }

}
