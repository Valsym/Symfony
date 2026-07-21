<?php

namespace App\Service;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class MailerService
{
    private $mailer;
    private $logger;

    public function __construct(string $mailerDsn, LoggerInterface $logger)
    {
        $transport = Transport::fromDsn($mailerDsn);
        $this->mailer = new Mailer($transport);
        $this->logger = $logger;
    }

    public function send(string $to, string $subject, string $body): void
    {
        try {
            $email = (new Email())
                ->from('admin_service@ya.ru')
                ->to($to)
                ->subject($subject)
                ->text($body);

            $this->mailer->send($email);
            $this->logger->info('Email sent to ' . $to);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email: ' . $e->getMessage());
            throw $e;
        }
    }
}

