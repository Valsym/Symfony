<?php

namespace App\Service;

class NotificationService
{
    private LoggerService $logger;

    private string $email;

    public function __construct(LoggerService $logger, string $email)
    {
        $this->logger = $logger;
        $this->email = $email;
    }

    public function sendNotification(string $message): string
    {
        $this->logger->log("Отправлено: $message");
        return "Уведомление отправлено: $message    email:" .  $this->email;
    }

}
