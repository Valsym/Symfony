<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
//use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Этот метод может быть пустым - Symfony перехватит его
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function loginApi(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $JWTManager
    ): JsonResponse
    {
        // Получаем данные из тела запроса
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Ищем пользователя по email
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        // Проверяем существование пользователя
        if (!$user) {
            return $this->json([
                'error' => 'Пользователь не найден'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Проверяем пароль
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'error' => 'Неверный пароль'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Генерируем JWT токен
        $token = $JWTManager->create($user);

        // Возвращаем токен
        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
        // Аутентификация через событие Symfony
        //return $this->json(['token' => 'ВАШ_JWT_ТОКЕН']);
    }
}
