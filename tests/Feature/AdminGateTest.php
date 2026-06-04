<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_users_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_users_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_regular_user_cannot_access_invite_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.invite'))
            ->assertForbidden();
    }

    public function test_admin_can_access_invite_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('users.invite'))
            ->assertOk();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
