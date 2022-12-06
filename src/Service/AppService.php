<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Image;
use App\Entity\User;
use App\Repository\ImageRepository;
use App\Security\EmailVerifier;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class AppService
{
    public const PATH_IMAGES = '/../../public/images';
    public const PATH_AVATAR = '/../../public/avatar';
    public const PATH_CONFIG_REGISTRATION = '/../../config/registration.php';

    public function __construct(
        private ManagerRegistry $doctrine,
    ) {
        $this->entityManager = $this->doctrine->getManager();
    }

    public function getNewImageAccount(int $idAccount): Image
    {
        $image = new Image();
        $image->setAccount($this->getAccount($idAccount));
        return $image;
    }

    public function getAccount(?int $idAccount = null, ?int $idUser = null): null|array|Account
    {
        $repository = $this->doctrine->getRepository(Account::class);
        if ($idAccount === null && $idUser === null) {
            return $repository->findAll();
        } elseif ($idAccount !== null) {
            return $repository->find($idAccount);
        }
        return $repository->findOneBy(['users_id' => $idUser]);
    }

    public function delImage($idImage): void
    {
        /**
         * @var ImageRepository $repository ;
         */
        $repository = $this->doctrine->getRepository(Image::class);
        $image = $repository->find($idImage);
        $repository->remove($image, true);
    }

    public function dataWriteImage(Form $form): void
    {
        /**
         * @var UploadedFile $file
         */
        $file = $form['path']->getData();
        /**
         * @var Image $image
         */
        $image = $form->getData();
        $nameFile = rand(1, 99999) . '_' . $image->getId() . '.' . $file->getClientOriginalExtension();
        $file->move(dirname(__FILE__) . self::PATH_IMAGES, $nameFile);
        $image->setPath($nameFile);
        $this->saveEntity($image);
    }

    public function saveEntity(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function getAccoutUser(int $idUser): Account
    {
        $repository = $this->doctrine->getRepository(Account::class);

        $result = $repository->findOneBy(['users_id' => $idUser]);
        if ($result === null) {
            // акаунта нет пользователя, создадим пустой
            $account = new Account();
            $account->setUsers($this->getUser($idUser));
            $account->setName('');
            $account->setFamily('');
            $account->setBiography('');
            $account->setAvatarPath('');
            // сохраним сущность
            $this->saveEntity($account);

            return $this->getAccoutUser($idUser);
        }

        return $result;
    }

    public function getUser(int $id): null|User
    {
        $repository = $this->doctrine->getRepository(User::class);
        return $repository->find($id);
    }

    public function dataWriteAccount(Form $form, ?string $avatarPath): void
    {
        /**
         * @var UploadedFile $file
         */
        $file = $form['avatar_path']->getData();
        /**
         * @var Account $account
         */
        $account = $form->getData();
        if ($file === null) {
            $nameFile = (string)$avatarPath;
        } else {
            // защита чтоб не выкачали аватары
            $nameFile = rand(1, 99999) . '_' . $account->getId() . '.' . $file->getClientOriginalExtension();
            $file->move(dirname(__FILE__) . self::PATH_AVATAR, $nameFile);
        }

        $account->setAvatarPath($nameFile);
        $this->saveEntity($account);
    }

    public function delUser(int $idUser): void
    {
        $user = $this->getUser($idUser);
        $account = $this->getAccount($user->getId());
        if ($account !== null) {
            $images = $this->getImagesAccount($account->getId());
            foreach ($images as $image) {
                $this->entityManager->remove($image);
            }
            $this->entityManager->remove($account);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function getImagesAccount(int $idAccount): array|Image
    {
        $repository = $this->doctrine->getRepository(Image::class);
        // нужно получить все фото.
        $result = $repository->findBy(['account_id' => $idAccount]);
        if ($result === null) {
            // нету у пользователя изображений создаем пустой обьект
            $image = new Image();
            $image->setAccount($this->getAccount($idAccount));
            $image->setPath('default.jpg');
            $this->saveEntity($image);

            return $this->getImagesAccount($idAccount);
        }

        return $result;
    }

    public function userHashPassword(
        string $passwordString,
        User $user,
        UserPasswordHasher $userPasswordHasher
    ) {
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $passwordString
            )
        );

        return $user;
    }

    public function sendEmailRegistration(EmailVerifier $emailVerifier, User $user)
    {
        $registration = $this->getRegistrationConfig();
        $emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address($registration->fromEmail, $registration->fromName))
                ->to($user->getEmail())
                ->subject($registration->subject)
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }

    public function getRegistrationConfig(): \ArrayObject
    {
        $result = new \ArrayObject();
        foreach (require dirname(__FILE__) . self::PATH_CONFIG_REGISTRATION as $key => $value) {
            $result->{$key} = $value;
        }
        return $result;
    }

}