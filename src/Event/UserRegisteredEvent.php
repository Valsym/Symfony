<?php

namespace App\Event;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\User;

class UserRegisteredEvent extends Event
{
    public const NAME = 'user.registered';

    private $user;
    //private $logger;

    public function __construct(User $user)//, LoggerInterface $logger)
    {
        $this->user = $user;
//        $this->logger = $logger;
    }

    public function getUser(): User
    {
//        $this->logger->info(
//            'class UserRegisteredEvent: произошло событие - регистрация пользователя',
//            ['email' => $this->user->getEmail()]
//        );

        return $this->user;

    }
}
