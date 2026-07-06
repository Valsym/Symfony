<?php
namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends AbstractController
{
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Сохраняем в сессию вместо БД
            $articles = $request->getSession()->get('articles', []);
            $article->id = count($articles) + 1; // Добавляем временный id
            $articles[] = $article;
            $request->getSession()->set('articles', $articles);

//            $em->persist($article);
//            $em->flush();

            $this->addFlash('success', 'Статья сохранена!');
            return $this->redirectToRoute('app_hello');
//            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
