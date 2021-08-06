<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\Security\Passport;

use Happyr\Auth0Bundle\Model\UserInfo;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class Auth0Badge implements BadgeInterface
{
    private UserInfo $user;

    public function __construct(UserInfo $user)
    {
        $this->user = $user;
    }

    public function getUserInfo(): UserInfo
    {
        return $this->user;
    }

    public function isResolved(): bool
    {
        return true;
    }
}
