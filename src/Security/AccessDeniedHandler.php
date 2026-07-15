<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    )
    {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // Добавляем flash сообщение
        $request->getSession()->getFlashBag()->add('danger', 'У вас нет прав для доступа к этой странице.');

        // Редирект на страницу с сообщением
        return new RedirectResponse($this->urlGenerator->generate('app_access_denied'));
    }
}
