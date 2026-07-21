<?php

namespace App\EventListener;

use App\Event\UserRegisteredEvent;
use App\Service\MailerService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Psr\Log\LoggerInterface;

class SendWelcomeEmailListener
{
    private $mailerService;
    //private $mailer;
    private $logger;

    public function __construct(MailerService $mailerService, LoggerInterface $logger)
//    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailerService = $mailerService;
//        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $this->logger->info('TEST: SendWelcomeEmailListener called');

        try {
            $user = $event->getUser();
            $this->logger->info('class SendWelcomeEmailListener: Просмотр пользователя', ['user_id' => $user->getId()]);

            $this->mailerService->send(
                $user->getEmail(),
                'Добро пожаловать!',
                'Спасибо за регистрацию!'
            );
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
