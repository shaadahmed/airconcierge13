<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
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
