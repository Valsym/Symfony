<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            if ($i === 0) {
                $user->setRoles(['ROLE_ADMIN']);
                $user->setEmail("admin@ya.com");
            } else {
                $user->setEmail("user{$i}@example.com");
            }
            // Хэшируем пароль!
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            //$user->setPassword('password');
            $user->setAvatar('uploads/avatars/default-avatar.png'); // Путь к дефолтному аватару
            // ...
            $manager->persist($user);  // <- ЭТО НУЖНО

        }

        $manager->flush();  // <- И ЭТО
    }

}
