<?php

namespace App\Service;

class LoggerService
{
    public function log(string $message): void
    {
        file_put_contents('log.txt', $message . PHP_EOL, FILE_APPEND);
    }
}
