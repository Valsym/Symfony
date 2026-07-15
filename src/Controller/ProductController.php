<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFilterType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
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

// Получаем данные напрямую из запроса
//        $name = $request->query->get('name');
//        $minPrice = $request->query->get('minPrice');
//        $maxPrice = $request->query->get('maxPrice');

        // Если нужно использовать фильтр через форму
        $name = $filterForm->get('name')->getData();
        $minPrice = $filterForm->get('minPrice')->getData();
        $maxPrice = $filterForm->get('maxPrice')->getData();
        $category = $filterForm->get('category')->getData();

        $query = $productRepository->search($name, $minPrice, $maxPrice, $category)->getQuery();
        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
        ]);
//
//
//        $query = $productRepository->createQueryBuilder('p')->getQuery();
//        $pagination = $paginator->paginate(
//            $query,
//            $request->query->getInt('page', 1), // Номер страницы
//            10 // Количество элементов на странице
//        );
//
//        return $this->render('product/index.html.twig', [
//            'pagination' => $pagination,
//        ]);

        // было без пагинации:
//        return $this->render('product/index.html.twig', [
//            'products' => $productRepository->findAll(),
//        ]);
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
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
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
}
