<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        private UserPasswordHasherInterface $hashPassword
    ) {
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $objectManager)
    {
        foreach ($this->dataUsers() as $user) {
            $newUser = new User();
            $newUser->setEmail($user->email);
            $newUser->setPassword($this->hashPassword->hashPassword($newUser, $user->password));
            $newUser->setRoles($user->role);
            $newUser->setIsVerified($user->isVerified);
            $objectManager->persist($newUser);
            $this->createAccounts($newUser, $objectManager);
        }
        $objectManager->flush();
    }

    private function dataUsers(): \ArrayObject
    {
        return new \ArrayObject([
            $this->getUserCollection('user@example.com', ['ROLE_USER'], '123654', true),
            $this->getUserCollection('user1@example.com', ['ROLE_USER'], '123654', true),
            $this->getUserCollection('userAdmin@example.com', ['ROLE_ADMIN'], '123654', true),
        ]);
    }

    private function getUserCollection(
        string $email,
        array $role,
        string $password,
        bool $isVerified
    ): \ArrayObject {
        $collection = new \ArrayObject();
        $collection->email = $email;
        $collection->role = $role;
        $collection->password = $password;
        $collection->isVerified = $isVerified;
        return $collection;
    }

    private function createAccounts(User $user, ObjectManager $objectManager)
    {
        $account = new Account();
        $account->setUsers($user);
        $account->setName($this->faker->firstName . '_' . $user->getId());
        $account->setFamily($this->faker->lastName);
        $account->setAvatarPath($this->faker->word . '.jpg');
        $account->setBiography($this->faker->realText());
        $objectManager->persist($account);
        $objectManager->flush();
        $this->loadImageInAccount($account, $objectManager);
    }

    private function loadImageInAccount(Account $account, ObjectManager $objectManager)
    {
        for ($i = 0; $i < rand(2, 8); $i++) {
            $image = new Image();
            $image->setAccount($account);
            $image->setPath($this->faker->word . '_' . $account->getId() . '.jpg');
            $objectManager->persist($image);
        }
        $objectManager->flush();
    }
}