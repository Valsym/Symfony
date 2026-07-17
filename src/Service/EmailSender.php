<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailSender implements EmailSenderInterface
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function sendConfirmationEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('admin@yandex.ru', 'Admin'))
            ->to((string) $user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        $this->mailer->send($email);
    }
}

