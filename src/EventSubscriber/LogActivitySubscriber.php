<?php

namespace App\EventSubscriber;

use App\Event\UserRegisteredEvent;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogActivitySubscriber implements EventSubscriberInterface
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::NAME => 'logUserRegistration',
        ];
    }

    public function logUserRegistration(UserRegisteredEvent $event): void
    {
        $userEmail = $event->getUser()->getEmail();
        // ... логика логирования
        $result = $this->notificationService->sendNotification("class LogActivitySubscriber: Новый юзер $userEmail зарегился");
    }
}
