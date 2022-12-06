<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AccountDTO;
use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(private AppService $service)
    {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $accounts = $this->service->getAccount();
        return $this->render('admin/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    #[Route('/admin/deluser/{idUser}', name: 'app_admin_del_user')]
    public function delUser(Request $request)
    {
        $idUser = (int) $request->get('idUser');
        $user = $this->service->getUser($idUser);
        // проверим чтоб пользователь не могу удалить себя
        if($user === $this->getUser()) {
            throw $this->createAccessDeniedException('Пользовтель не может удалить сам себя');
        }
        $this->service->delUser($idUser);
        $this->addFlash('del_user', AccountDTO::DEL_USER);
        return $this->redirectToRoute('app_admin');
    }
}
