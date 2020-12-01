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
}
