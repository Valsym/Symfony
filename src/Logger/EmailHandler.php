<?php
// src/Logger/EmailHandler.php

namespace App\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailHandler extends AbstractProcessingHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private string $from,
        private string $to,
        private string $subject
    ) {
        parent::__construct();
    }

    protected function write(LogRecord|array $record): void
    {

        $email = (new Email())
            ->from($this->from)
            ->to($this->to)
            ->subject($this->subject)
            ->text($record['message']);
//            ->text($record['formatted'] ?? $record['message']);

        $this->mailer->send($email);
    }
}

