<?php

namespace App\Logger;

use App\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DoctrineHandler extends AbstractProcessingHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function write(array|LogRecord $record): void
    {
        try {
            $logEntry = new LogEntry();
            $logEntry->setMessage($record['message']);
            $logEntry->setLevel($record['level_name']);

            $this->em->persist($logEntry);
            $this->em->flush();
        } catch (\Exception $e) {
            // Пишем в стандартный лог ошибок PHP
            error_log('DoctrineHandler error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }

    }
}
