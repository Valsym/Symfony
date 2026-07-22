<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@example.com");
            $user->setPassword('password');
            $user->setAvatar('uploads/avatars/default-avatar.png'); // Путь к дефолтному аватару
            // ...
            $manager->persist($user);  // <- ЭТО НУЖНО

        }

        $manager->flush();  // <- И ЭТО
    }

}
