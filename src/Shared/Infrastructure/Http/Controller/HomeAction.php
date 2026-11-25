<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The home page, /fr and /en.
 *
 * @see HomeActionTest
 */
final class HomeAction extends AbstractController
{
    #[Route(path: '/', name: self::class, methods: ['GET', 'HEAD'])]
    public function __invoke(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
