<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AccountDTO;
use App\Form\AccountFormType;
use App\Form\AlbumFormType;
use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account/{id}', name: 'app_account', defaults: ['id' => 0])]
    public function index(AppService $service, Request $request): Response
    {
        // пользователь должен быть зарегистрирован в системе для просмотра аккаунта
        if ($this->getUser() === null) {
            $this->addFlash('no_login', AccountDTO::NO_LOGIN);
            return $this->redirectToRoute('app_login');
        }
        $idUserRequest = (int)$request->get('id');
        if ($idUserRequest === 0) {
            $idUserRequest = $this->getUser()->getId();
        }

        $account = $service->getAccoutUser($this->getUser()->getId());
        // проверяем если пользователь не сообственик акаунта ему просто показываем акаунт
        if ($idUserRequest !== $this->getUser()->getId()) {
            $account = $service->getAccoutUser($idUserRequest);
            return $this->render('account/view.html.twig', [
                'account' => $account,
            ]);
        }

        $avatarPath = $account->getAvatarPath();
        // создаем форму на основе данных акунта пользователя
        $form = $this->createForm(AccountFormType::class, $account);

        // обработка формы
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $service->dataWriteAccount($form, $avatarPath);
            $this->addFlash('account_update', AccountDTO::ACCOUNT_UPDATE);
            return $this->redirectToRoute('app_account', ['id' => $this->getUser()->getId()]);
        }

        return $this->renderForm('account/index.html.twig', [
                'form' => $form,
                'account' => $account
            ]
        );
    }

    #[Route('/account/album/{id}', name: 'app_account_album')]
    public function album(AppService $service, Request $request): Response
    {
        // только зарегистрированные пльзователи могу смотреть фото в альбомах
        if ($this->getUser() === null) {
            $this->addFlash('no_login', AccountDTO::VIEW_ALBUM_IS_LOGIN);
            return $this->redirectToRoute('app_login');
        }
        // id Это id User
        $account = $service->getAccount(null, (int)$request->get('id'));
        // Проверим пользователя ли акаунт
        $accountIsUser = $account->getUsers()->getId() === $this->getUser()->getId() ? true : false;
        // Получаем объект изображения
        $image = $service->getNewImageAccount($account->getId());
        $form = $this->createForm(AlbumFormType::class, $image);
        // обработка формы
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // только пользователь может удалиь
            if (!$accountIsUser) {
                throw $this->createAccessDeniedException();
            }
            $service->dataWriteImage($form);
            $this->addFlash('loaded_image', AccountDTO::LOADED_IMAGE);
            return $this->redirectToRoute('app_account_album', ['id' => $account->getUsers()->getId()]);
        }

        return $this->renderForm('account/album.html.twig', [
                'images' => $service->getImagesAccount($account->getId()),
                'accountIsUser' => $accountIsUser,
                'form' => $form,
                'account' => $account
            ]
        );
    }

    #[Route('/account/delimage/{id}/{iduser}', name: 'app_account_delimage')]
    public function delimage(AppService $service, Request $request): RedirectResponse
    {
        // только зарегистрированные пльзователи могут удалять свои фото
        if ($this->getUser() === null) {
            $this->addFlash('no_login', AccountDTO::ONLY_DELETE_OWNER);
            return $this->redirectToRoute('app_login');
        }
        $idImage = (int)$request->get('id');
        $idUser = (int)$request->get('iduser');
        // только пользователь может удалиь
        if ($idUser !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }
        $service->delImage($idImage);
        $this->addFlash('del_image', AccountDTO::DEL_IMAGE);

        return $this->redirectToRoute('app_account_album', ['id' => $idUser]);
    }
}
