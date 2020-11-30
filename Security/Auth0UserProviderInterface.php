<?php

declare(strict_types=1);

namespace Happyr\Auth0Bundle\Security;

use Happyr\Auth0Bundle\Model\UserInfo;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Auth0UserProviderInterface
{
    /**
     * @throws UsernameNotFoundException if the user is not found
     *
     * @return UserInterface
     */
    public function loadByUserModel(UserInfo $userInfo);
}
