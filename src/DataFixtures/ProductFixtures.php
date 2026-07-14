<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            $product->setName($faker->words(3, true)); // Случайное название
            $product->setPrice($faker->randomFloat(2, 5, 500)); // Цена от 5 до 500
            $product->setDescription($faker->paragraph()); // Случайное описание

            $manager->persist($product);
        }

        // либо Быстрый способ - цикл
//        for ($i = 1; $i <= 50; $i++) {
//            $product = new Product();
//            $product->setName("Product {$i}");
//            $product->setPrice(mt_rand(100, 10000) / 100); // цена от 1.00 до 100.00
//            $product->setDescription("Description for product {$i}");
//
//            $manager->persist($product);
//        }

        $manager->flush();
    }
}
