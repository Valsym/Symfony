<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Сначала создаем категории
        $categories = ['Electronics', 'Books', 'Clothing', 'Home'];
        $categoryEntities = [];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
            $categoryEntities[] = $category;
        }

        // Сохраняем категории в БД
        $manager->flush();

        // 2. Теперь создаем продукты
        $faker = Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            $product->setName($faker->words(3, true));
            $product->setPrice($faker->randomFloat(2, 5, 500));
            $product->setDescription($faker->paragraph());

            // Случайная категория
            $randomIndex = array_rand($categoryEntities);
            $product->setCategory($categoryEntities[$randomIndex]);

            $manager->persist($product);
        }

        $manager->flush();
    }
    // Указываем, что эта фикстура зависит от CategoryFixture
//    public function getDependencies(): array
//    {
//        return [
//            CategoryFixture::class,
//        ];
//    }
//    public function loadOld(ObjectManager $manager): void
//    {
//        $faker = Factory::create();
//
//        for ($i = 0; $i < 50; $i++) {
//            $product = new Product();
//            $product->setName($faker->words(3, true)); // Случайное название
//            $product->setPrice($faker->randomFloat(2, 5, 500)); // Цена от 5 до 500
//            $product->setDescription($faker->paragraph()); // Случайное описание
//
//            // Случайная категория из 4 существующих
//            $randomCategoryKey = rand(0, 3); // 0, 1, 2, 3 - столько же, сколько категорий
//            $category = $this->getReference('category_' . $randomCategoryKey, CategoryFixture::class);
////            $category = $this->getReference(CategoryFixture::class, 'category_' . $randomCategoryKey);
////            $category = $this->getReference('category_' . $randomCategoryKey);
//            $product->setCategory($category);
//
//            $manager->persist($product);
//        }

        // либо Быстрый способ - цикл
//        for ($i = 1; $i <= 50; $i++) {
//            $product = new Product();
//            $product->setName("Product {$i}");
//            $product->setPrice(mt_rand(100, 10000) / 100); // цена от 1.00 до 100.00
//            $product->setDescription("Description for product {$i}");
//
//            $manager->persist($product);
//        }

//        $manager->flush();
//    }
}
