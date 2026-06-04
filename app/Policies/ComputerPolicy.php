<?php

namespace App\Policies;

use App\Models\Computer;
use App\Models\User;

class ComputerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Computer $computer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Computer $computer): bool
    {
        return true;
    }

    public function delete(User $user, Computer $computer): bool
    {
        return $user->isAdmin();
    }
}
