<?php

namespace App\Policies\Concerns;

use App\Enums\UserRole;
use App\Models\User;

trait AuthorizesStaff
{
    protected function isStaff(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Manager => true,
            default => false,
        };
    }

    protected function isSuperAdmin(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    protected function isAdminOrAbove(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin, UserRole::Admin => true,
            default => false,
        };
    }
}
