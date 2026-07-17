<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Service\EmailSenderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testRegistrationForm()
    {
        $client = static::createClient();
        $client->disableReboot(); // <-- обязательно!

        // Создаем мок для EmailSender
        $emailSenderMock = $this->createMock(EmailSenderInterface::class);
        $emailSenderMock->expects($this->once())
            ->method('sendConfirmationEmail')
            ->with($this->isInstanceOf(User::class));

        // Подменяем сервис в контейнере
        $client->getContainer()->set(EmailSenderInterface::class, $emailSenderMock);
        // После подмены
//        dump($client->getContainer()->get(EmailSenderInterface::class)); // должен показать mock



        // Подменяем EmailVerifier на мок
//        $emailVerifierMock = $this->createMock(EmailVerifier::class);
//        $client->getContainer()->set(EmailVerifier::class, $emailVerifierMock);


        // Генерируем уникальный email
        $uniqueEmail = 'user_' . uniqid() . '@example.com';

        //$client->catchExceptions(false); // Показывать все исключения
        $crawler = $client->request('GET', '/register');

        // Если ошибка - покажи
        if ($client->getResponse()->getStatusCode() === 500) {
            dump($client->getResponse()->getContent());
            die;
        }
        // Покажи все поля формы
//        $form = $crawler->filter('form[name="registration_form"]')->form();
//        dump($form->getValues());
//        die;

        $form = $crawler->selectButton('Register')->form();
        $form['registration_form[email]'] = 'not-an-email';
        $form['registration_form[plainPassword]'] = '123';
        $form['registration_form[agreeTerms]'] = '1';


        $client->submit($form);

        // Проверяем, что есть ошибки валидации
        $this->assertSelectorCount(2, '.invalid-feedback');

        // Тест успешной отправки
        $form['registration_form[email]'] = $uniqueEmail;
        $form['registration_form[plainPassword]'] = 'SecurePassword123!';
        $form['registration_form[agreeTerms]'] = true;

        $client->submit($form);

        // Посмотри, что возвращает
//        $response = $client->getResponse();
//        dump($response->getContent()); // весь html
//        die;

        $this->assertResponseRedirects('/product');
        // Проверяем, что пользователь создан в базе
        $user = $client->getContainer()->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => $uniqueEmail]); // используем $uniqueEmail

        $this->assertNotNull($user);
    }

}
