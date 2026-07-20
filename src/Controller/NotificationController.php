<?php

namespace App\Controller;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\TransportInterface;

#[Route('/note')]
class NotificationController extends AbstractController
{
    #[Route(name: 'app_note_send', methods: ['GET'])]
    public function send(NotificationService $notificationService): Response
    {
        $result = $notificationService->sendNotification('Новое сообщение');
        return new Response($result);
    }

    #[Route('/debug-mailer', name: 'debug_mailer')]
    public function debugMailer(): Response
    {
        // Не используй getParameter('mailer_dsn') — его нет!
        $mailerDsn = $_ENV['MAILER_DSN'] ?? $_SERVER['MAILER_DSN'] ?? 'NOT SET';
        $envVar = $_ENV['MAILER_DSN'] ?? 'NOT SET';

        return new Response(
            "MAILER_DSN env: $mailerDsn"
        );
    }

    #[Route('/send-test', name: 'send_test')]
    public function sendTest(MailerInterface $mailer, LoggerInterface $logger): Response
    {
        try {
            $logger->info('sendTest: Starting...');

            $email = (new Email())
                ->from('test@example.com')
                ->to('user@example.com')
                ->subject('Test from Symfony')
                ->text('This is a test email');

            $logger->info('sendTest: Sending...');
            $mailer->send($email);
            $logger->info('sendTest: Sent!');

            return new Response('Email sent successfully!');
        } catch (\Exception $e) {
            $logger->error('sendTest error: ' . $e->getMessage());
            return new Response('Error: ' . $e->getMessage(), 500);
        }
    }



    #[Route('/force-test', name: 'force_test')]
    public function forceTest(LoggerInterface $logger): Response
    {
        try {
            // Создаем транспорт вручную, игнорируя конфиг
            $transport = Transport::fromDsn('smtp://null:null@127.0.0.1:1025');
            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from('test@test.com')
                ->to('user@test.com')
                ->subject('Force test')
                ->text('Force test body');

            $mailer->send($email);
            $logger->info('Force test: SUCCESS');

            return new Response('Force test: OK');
        } catch (\Exception $e) {
            $logger->error('Force test error: ' . $e->getMessage());
            return new Response('Error: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/debug-transport', name: 'debug_transport')]
    public function debugTransport(TransportInterface $transport, LoggerInterface $logger): Response
    {
        $reflection = new \ReflectionClass($transport);
        $logger->info('Transport class: ' . get_class($transport));

        // Если это AggregateTransport — покажи все транспорты
        if ($transport instanceof \Symfony\Component\Mailer\Transport\AggregateTransport) {
            $logger->info('Transport DSNS: ' . json_encode($transport->getTransports()));
        }

        return new Response('Transport class: ' . get_class($transport));
    }



    #[Route('/ultimate-test', name: 'ultimate_test')]
    public function ultimateTest(MailerInterface $mailer, LoggerInterface $logger): Response
    {
        try {
            // 1. Смотрим, что за транспорт внутри MailerInterface
            $reflection = new \ReflectionClass($mailer);

            // Получаем свойство transport (оно приватное)
            $transportProperty = $reflection->getProperty('transport');
            $transportProperty->setAccessible(true);
            $transport = $transportProperty->getValue($mailer);

            $logger->info('ULTIMATE TEST: Transport class: ' . get_class($transport));

            // 2. Создаем новый транспорт из того же DSN
            $dsn = 'smtp://null:null@127.0.0.1:1025';
            $newTransport = Transport::fromDsn($dsn);
            $newMailer = new Mailer($newTransport);

            // 3. Отправляем через новый транспорт
            $email = (new Email())
                ->from('test@test.com')
                ->to('user@test.com')
                ->subject('Ultimate test')
                ->text('Ultimate test body');

            $logger->info('ULTIMATE TEST: Trying with manually created transport...');
            $newMailer->send($email);
            $logger->info('ULTIMATE TEST: Manual send SUCCESS!');

            // 4. Теперь пробуем через инжектированный mailer
            $email2 = (new Email())
                ->from('test2@test.com')
                ->to('user2@test.com')
                ->subject('Ultimate test 2')
                ->text('Ultimate test body 2');

            $logger->info('ULTIMATE TEST: Trying with injected mailer...');
            $mailer->send($email2);
            $logger->info('ULTIMATE TEST: Injected send SUCCESS!');

            return new Response('Both sent! Check logs and Mailpit');
        } catch (\Exception $e) {
            $logger->error('ULTIMATE TEST error: ' . $e->getMessage());
            return new Response('Error: ' . $e->getMessage(), 500);
        }
    }

}
