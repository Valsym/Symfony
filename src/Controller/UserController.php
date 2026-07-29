<?php
namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Возвращает список юзеров'
    )]
    #[Route('/api/users', name: 'api_users', methods: ['GET'])]
    public function index(UserRepository $repository): JsonResponse
    {
        $users = $repository->findAll();
        return $this->json($users, 200, [], ['groups' => ['public']]);

//        return $this->json($products);

    }

    #[OA\Response(
        response: 200,
        description: 'Возвращает юзера по ID'
    )]
    #[OA\Response(
        response: 404,
        description: 'Юзер не найден'
    )]
    #[Route('/api/users/{id}', name: 'api_user_show', methods: ['GET'])]
    public function show(User $user, SerializerInterface $serializer): Response
    {
        $data = $serializer->serialize($user, 'json', [
            'datetime_format' => 'Y-m-d H:i:s',
            'groups' => ['public'],  // <- добавить groups здесь
        ]);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

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

    #[Route('/test_admin', name: 'test_admin')]
    public function testAdmin(UserRepository $userRepository): Response
    {
        return $this->render('user/admin.html.twig', [
            'error' => null,  // или передать ошибку, если нужно
        ]);
    }

    #[Route('/access-denied', name: 'app_access_denied')]
    public function accessDenied(): Response
    {
        return $this->render('user/access_denied.html.twig', [
            'message' => 'Доступ запрещен. У вас нет прав для просмотра этой страницы.'
        ]);
    }


}
