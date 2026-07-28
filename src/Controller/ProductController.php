<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFilterType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, PaginatorInterface $paginator): Response
    {
        $filterForm = $this->createForm(ProductFilterType::class, null, [
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);

        // Отладка - раскомментируй для проверки
        // dd($filterForm->getData(), $request->query->all());

        // Если нужно использовать фильтр через форму
        $name = $filterForm->get('name')->getData();
        $minPrice = $filterForm->get('minPrice')->getData();
        $maxPrice = $filterForm->get('maxPrice')->getData();
        $category = $filterForm->get('category')->getData();

        // Определяем, используем ли кэш
        $useCache = !$name && !$minPrice && !$maxPrice && !$category;

        if ($useCache) {
            // Берём из кэша
            $products = $productRepository->findCachedProducts();

            // Ручная пагинация для массива (т.к. из кэша возвращается массив)
            $page = $request->query->getInt('page', 1);
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $paginatedProducts = array_slice($products, $offset, $limit);
            $total = count($products);

            $pagination = $paginator->paginate($paginatedProducts, $page, $limit);
            $pagination->setTotalItemCount($total); // Устанавливаем общее количество
        } else {
            // Запрос в БД с фильтрацией
            $query = $productRepository->search($name, $minPrice, $maxPrice, $category)->getQuery();
            $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);
        }
//        $query = $productRepository->search($name, $minPrice, $maxPrice, $category)->getQuery();
//        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            //  $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product, Request $request): Response
    {
        $response = new Response($this->renderView('product/show.html.twig', [
            'product' => $product,
        ]));

        // Генерируем ETag на основе всех изменяемых полей
        $etag = md5(
            $product->getName() .
            $product->getPrice() .
            $product->getDescription() .
            ($product->getCategory() ? $product->getCategory()->getId() : 'null')
        );
        // Или на основе хеша от всего объекта (сериализация)
        $etag = md5(serialize($product));
        $response->setEtag($etag);

        // Генерируем ETag на основе содержимого статьи
//        $etag = md5($response->getContent());
//        $response->setEtag($etag);
        // или ETag на основе времени последнего обновления
//        $etag = md5($product->getUpdatedAt()->format('Y-m-d H:i:s'));
//        $response->setEtag($etag);

        // Проверяем, актуален ли кэш у клиента
        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;

//        return $this->render('product/show.html.twig', [
//            'product' => $product,
//        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
//            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
//            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    public function someAction(LoggerInterface $logger): Response
    {
        $logger->warning('Тестовое сообщение WARNING уровня');
        // или
        $logger->error('Тестовое сообщение ERROR уровня');

        return Response::create('Тестовое сообщение');
    }
}
