<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Hash;

class MeControllerTest extends ApiTestCase
{
    public function test_me_payload_endpoint_is_accessible(): void
    {
        $this->apiJson('GET', $this->apiRoute('me', 'payload'))->assertOk();
    }

    public function test_me_show_and_update_profile(): void
    {
        $this->apiJson('GET', $this->apiRoute('me', 'show'))
            ->assertOk()
            ->assertJsonPath('id', $this->user->id)
            ->assertJsonMissingPath('password');

        $this->apiJson('PATCH', $this->apiRoute('me', 'update'), [], [
            'email' => 'changed@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors(['current_password']);

        $this->apiJson('PATCH', $this->apiRoute('me', 'update'), [], [
            'name' => 'Root Operator',
        ])->assertOk()->assertJsonPath('name', 'Root Operator');

        $this->apiJson('PATCH', $this->apiRoute('me', 'update'), [], [
            'email' => 'changed@example.com',
            'current_password' => $this->password,
        ])->assertOk()->assertJsonPath('email', 'changed@example.com');
        $this->user->refresh();

        $this->apiJson('PATCH', $this->apiRoute('me', 'update'), [], [
            'current_password' => $this->password,
            'password' => 'new-secret-pass',
            'password_confirmation' => 'new-secret-pass',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-secret-pass', $this->user->refresh()->password));
    }

    public function test_me_put_requires_full_profile_payload(): void
    {
        $this->apiJson('PUT', $this->apiRoute('me', 'update'), [], [
            'name' => 'Only Name',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);

        $this->apiJson('PUT', $this->apiRoute('me', 'update'), [], [
            'name' => 'Full Replacement',
            'email' => 'full@example.com',
            'current_password' => $this->password,
        ])->assertOk()
            ->assertJsonPath('name', 'Full Replacement')
            ->assertJsonPath('email', 'full@example.com');
    }
}
