<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Chronology;
use App\Models\User;

class ChronologyPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Chronology $chronology): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Chronology $chronology): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Chronology $chronology): bool
    {
        return $this->isStaff($user);
    }

    private function isStaff(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Manager => true,
            default => false,
        };
    }
}
