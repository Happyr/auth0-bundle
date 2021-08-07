<?php

namespace Happyr\Auth0Bundle\Tests\Unit\Model;

use Happyr\Auth0Bundle\Model\UserInfo;
use PHPUnit\Framework\TestCase;

class UserInfoTest extends TestCase
{
    public function testGetUserId()
    {
        $user = UserInfo::create(['user_id' => 'auth0|abc']);
        $this->assertEquals('auth0|abc', $user->getUserId());
    }

    public function testGetLoginIdentifier()
    {
        $user = UserInfo::create(['user_id' => 'auth0|abc', 'sub' => 'google|123']);
        $this->assertEquals('auth0|abc', $user->getLoginIdentifier());

        $user = UserInfo::create(['sub' => 'google|123']);
        $this->assertEquals('google|123', $user->getLoginIdentifier());
    }
}
