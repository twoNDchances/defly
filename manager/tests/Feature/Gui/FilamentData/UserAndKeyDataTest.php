<?php

namespace Tests\Feature\Gui\FilamentData;

use Tests\Support\KeyDataHarness;
use Tests\Support\UserDataHarness;
use Tests\TestCase;

class UserAndKeyDataTest extends TestCase
{
    public function test_user_and_key_edit_forms_drop_empty_sensitive_values(): void
    {
        $this->assertArrayNotHasKey('password', UserDataHarness::editForm(['password' => '']));
        $this->assertSame('secret', UserDataHarness::editForm(['password' => 'secret'])['password']);
        $this->assertArrayNotHasKey('token', KeyDataHarness::editForm(['token' => '']));
        $this->assertSame('secret-token', KeyDataHarness::editForm(['token' => 'secret-token'])['token']);
    }
}
