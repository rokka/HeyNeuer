<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'email',
    'password',
    'is_admin',
    'registered_at',
    'last_login_at',
    'invited_at',
    'invitation_token',
])]
#[Hidden(['password', 'remember_token', 'invitation_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
            'registered_at'     => 'datetime',
            'last_login_at'     => 'datetime',
            'invited_at'        => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function hasAcceptedInvitation(): bool
    {
        return $this->registered_at !== null && $this->invitation_token === null;
    }

    public static function generateInvitationToken(): string
    {
        return Str::random(64);
    }
}
