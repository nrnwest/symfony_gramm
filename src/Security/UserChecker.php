<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }
        if ($user->getUserIdentifier() && !$user->isVerified()) {
            $response = new RedirectResponse('/unverified/email?id=' . $user->getId());
            $response->send();
            throw new CustomUserMessageAccountStatusException(
                'confirm your registration by mail, or go through it again.'
            );
        }
        /*    if ($user->isDeleted()) {
                // the message passed to this exception is meant to be displayed to the user
                throw new CustomUserMessageAccountStatusException('Your user account no longer exists.');
            }*/
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // user account is expired, the user may be notified
        /*        if ($user->isExpired()) {
                    throw new AccountExpiredException('...');
                }*/
    }
}