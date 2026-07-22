<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Service\EmailSenderInterface; // Добавь импорт
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\Autowire; // Добавь этот импорт

//use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    //private ?EmailVerifier $emailVerifier;

    public function __construct(
        private EmailSenderInterface $emailSender, // Используем интерфейс
        //?EmailVerifier $emailVerifier = null
    ) {
        //$this->emailVerifier = $emailVerifier;
    }
    //private ?EmailVerifier $emailVerifier = null;

//    public function __construct(?EmailVerifier $emailVerifier = null)
//    {
//        $this->emailVerifier = $emailVerifier;
//    }
//    public function __construct(private EmailVerifier $emailVerifier)
//    {
//    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             Security $security, EntityManagerInterface $entityManager,
                             EventDispatcherInterface $dispatcher,
                             #[Autowire(service: 's3_storage')] Filesystem $s3Storage): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                // Генерируем уникальное имя
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    // Открываем поток для чтения файла
                    $stream = fopen($avatarFile->getRealPath(), 'r+');

                    // Записываем в MinIO (S3)
                    $s3Storage->writeStream(
                        'avatars/' . $newFilename,  // используем уникальное имя, а не оригинальное
                        $stream
                    );
                    fclose($stream);

                    // Сохраняем путь в сущность (относительный путь в MinIO)
                    $user->setAvatar('avatars/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Ошибка при загрузке аватара: ' . $e->getMessage());
                    return $this->redirectToRoute('app_register');
                }
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // Отправляем email через наш сервис
            $this->emailSender->sendConfirmationEmail($user);

            // Авторизуем пользователя
            $security->login($user, 'form_login', 'main');

            // Создаем и диспатчим событие
            $event = new UserRegisteredEvent($user);
            $dispatcher->dispatch($event, UserRegisteredEvent::NAME);

            // Редиректим на главную
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }


    #[Route('/register_old', name: 'app_register_old')]
    public function registerOld(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                // Генерируем уникальное имя
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();

                // Перемещаем файл в uploads/avatars
                $avatarFile->move(
                    $this->getParameter('upload_directory') . '/avatars',
                    $newFilename
                );

                // Сохраняем путь в сущность
                $user->setAvatar('uploads/avatars/' . $newFilename);
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // Отправляем email через наш сервис
            $this->emailSender->sendConfirmationEmail($user);

            // Отправляем email только если emailVerifier доступен
//            if ($this?->emailVerifier) {
//                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
//                    (new TemplatedEmail())
//                        ->from(new Address('fanski@yandex.ru', 'fanski'))
//                        ->to((string)$user->getEmail())
//                        ->subject('Please Confirm your Email')
//                        ->htmlTemplate('registration/confirmation_email.html.twig')
//                );
//            }

            // do anything else you need here, like send an email

//            return
            $security->login($user, 'form_login', 'main');

            // Создаем и диспатчим событие
            $event = new UserRegisteredEvent($user);
            $dispatcher->dispatch($event, UserRegisteredEvent::NAME);

            // Редиректим на главную
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            //$this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
