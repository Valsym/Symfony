<?php
// src/Logger/TelegramHandler.php

namespace App\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramHandler extends AbstractProcessingHandler
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $token,
        private string $chatId
    ) {
        parent::__construct();
    }

    protected function write(LogRecord|array $record): void
    {
        $message = sprintf(
            "[%s] %s\n\n%s",
            $record['level_name'] ?? 'CRITICAL',
            $record['message'] ?? '',
            $record['formatted'] ?? ''
        );

        try {
            $this->httpClient->request('POST', "https://api.telegram.org/bot{$this->token}/sendMessage", [
                'json' => [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ],
                'timeout' => 10,  // Увеличили таймаут до 10 секунд
            ]);
        } catch (\Exception $e) {
            // Логируем ошибку в стандартный лог-файл
            error_log('TelegramHandler error: ' . $e->getMessage());
        }
//        $this->httpClient->request('POST', "https://api.telegram.org/bot{$this->token}/sendMessage", [
//            'json' => [
//                'chat_id' => $this->chatId,
//                'text' => $message,
//                'parse_mode' => 'HTML'
//            ]
//        ]);
    }
}

