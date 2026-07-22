<?php

namespace App\EventListener;

use App\Event\UserRegisteredEvent;
use App\Service\MailerService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;

class SendWelcomeEmailListener
{
    private $mailerService;
    private $mailer;
    private $logger;

    public function __construct(MailerInterface $mailer, MailerService $mailerService, LoggerInterface $logger)
//    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailerService = $mailerService;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $this->logger->info('TEST: SendWelcomeEmailListener called');

        try {
            $user = $event->getUser();
            $this->logger->info('class SendWelcomeEmailListener: Зарегился юзер', ['user_email' => $user->getEmail()]);

            $this->mailerService->send(
                $user->getEmail(),
                'Добро пожаловать!',
                'Спасибо за регистрацию!'
            );

            // Теперь пробуем через инжектированный mailer
            $email = (new Email())
                ->from('admin@test.com')
                ->to($user->getEmail())
                ->subject('Добро пожаловать!')
                ->text('Спасибо за регистрацию!');
            $this->mailer->send($email);

//            $email = (new TemplatedEmail())
//                ->to($user->getEmail())
//                ->subject('Добро пожаловать!')
//                ->htmlTemplate('emails/welcome.html.twig')
//                ->context([
//                    'user' => $user,
//                ]);

//            $this->logger->info('Attempting to send email...');
//            $this->mailer->send($email);
//            $this->logger->info('Email sent successfully!');

        } catch (\Exception $e) {
            $this->logger->error('Ошибка при отправке приветственного письма пользователю', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
