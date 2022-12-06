<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnverifiedEmailController extends AbstractController
{
    #[Route('/unverified/email', name: 'app_unverified_email')]
    public function index(Request $request, AppService $service): Response
    {
        $id = (int)$request->get('id');
        $user = $service->getUser($id);

        return $this->render('unverifiedEmail/index.html.twig', [
                'email' => $user->getEmail()
            ]
        );
    }
}
