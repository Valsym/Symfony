<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/product');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Product index');
    }

    public function testNewRedirectsAfterSubmit(): void
    {
        $client = static::createClient();

        // Открываем страницу создания продукта
        $crawler = $client->request('GET', '/product/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create new Product'); // или другой заголовок

        // Находим форму и заполняем её
        $form = $crawler->selectButton('Save')->form([
            'product[name]' => 'Test Product',
            'product[price]' => 99.99,
            'product[description]' => 'Test description',
            //'product[category]' => 'Home',
            // Добавьте другие поля если нужно (category и т.д.)
        ]);

        // Отправляем форму
        $client->submit($form);

        // Проверка, что редирект произошел (без указания конкретного URL)
        $this->assertResponseRedirects();

        // Проверяем, что произошел редирект на URL
        $this->assertResponseRedirects(
            '/product', // ожидаемый URL редиректа
            Response::HTTP_SEE_OTHER // 303 - статус редиректа
        );

        // Можно также проверить редирект по имени маршрута
        $this->assertResponseRedirects(
            $this->getContainer()->get('router')->generate('app_product_index'),
            Response::HTTP_SEE_OTHER
        );

        // Если нужно проверить, что данные сохранились в БД после редиректа
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'Test Product');
    }
}
