<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use Symfony\Component\HttpFoundation\Response;
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ProductCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Моя админка')
            ->setFaviconPath('favicon.ico')
            // Отключаем тему (светлая/тёмная)
            ->disableDarkMode()
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Главная', 'fa fa-home');
        yield MenuItem::linkToUrl('Товары', 'fas fa-box', $this->generateUrl('admin_product_index'));
        yield MenuItem::linkToUrl('Категории', 'fas fa-tags', $this->generateUrl('admin_category_index'));
        yield MenuItem::linkToUrl('Пользователи', 'fas fa-users', $this->generateUrl('admin_user_index'));

        yield MenuItem::section();
        yield MenuItem::linkToLogout('Выйти', 'fa fa-sign-out');
    }

}
