<?php

namespace SymfonyClient\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return new Response(
            '<html lang="es"><body>
                <h1>It works!</h1>
                <p><strong>Running PHP version:</strong> ' . PHP_VERSION . '</p>
                <p><strong>Running Symfony version:</strong> ' . Kernel::VERSION . '</p>
            </body></html>'
        );
    }
}
