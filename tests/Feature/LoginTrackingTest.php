<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_login_at_is_set_on_login_event(): void
    {
        $user = User::factory()->create(['last_login_at' => null]);

        event(new Login('web', $user, false));

        $this->assertNotNull($user->refresh()->last_login_at);
    }
}
