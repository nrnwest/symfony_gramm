<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(AppService $service): Response
    {
        $accounts = $service->getAccount();
        return $this->render('default/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }
}
