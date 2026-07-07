<?php
// test_query.php

require_once 'vendor/autoload.php';

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Dotenv\Dotenv;

// Загрузка .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

// Получаем EntityManager
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

// Тестируем запрос
$repository = $em->getRepository(User::class);
$date = new \DateTime('2023-01-01');
$users = $repository->findUsersRegisterAfterDate($date);

echo "Пользователи, зарегистрированные после 2023-01-01:\n";
foreach ($users as $user) {
    echo "- {$user->getEmail()} (зарегистрирован: {$user->getCreatedAt()->format('Y-m-d')})\n";
}
