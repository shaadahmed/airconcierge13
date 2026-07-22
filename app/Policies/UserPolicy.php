<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Role-based authorization for user / admin surfaces (ADR-010).
 */
class UserPolicy
{
    /**
     * Superadmin-only application surfaces.
     */
    public function accessSuperAdminArea(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin => true,
            default => false,
        };
    }

    /**
     * Owner statements placeholder (any authenticated role in Phase 1 shell).
     */
    public function viewOwnerStatements(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::Manager,
            UserRole::Owner,
            UserRole::Cleaner,
            UserRole::Maintenance => true,
        };
    }

    /**
     * Owner terms placeholder (any authenticated role in Phase 1 shell).
     */
    public function viewOwnerTerms(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::Manager,
            UserRole::Owner,
            UserRole::Cleaner,
            UserRole::Maintenance => true,
        };
    }
}
