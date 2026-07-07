<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            $user->setCreatedAt(new \DateTime());
            // Получаем пароль из unmapped поля
            $plainPassword = $form->get('password')->getData();

            // Хешируем пароль и устанавливаем
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTime());


            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Юзер сохранен!');
            return $this->redirectToRoute('app_hello');
//            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/test-users', name: 'test_users')]
    public function testUsers(UserRepository $userRepository): Response
    {
        $date = new \DateTime('2023-01-01');
        $users = $userRepository->findUsersRegisterAfterDate($date);

        // Выведем результат
        dd($users); // dump and die - покажет массив пользователей
    }

    #[Route('/test-find-users', name: 'test_find-users')]
    public function testFindUsers(UserRepository $userRepository): Response
    {
        $key = 'ski2';
        $users = $userRepository->findByName($key);

        // Выведем результат
        dd($users); // dump and die - покажет массив пользователей
    }

}
