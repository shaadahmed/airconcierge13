<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\Concerns\AuthorizesStaff;

/**
 * Role-based authorization for user / admin surfaces (ADR-010).
 */
class UserPolicy
{
    use AuthorizesStaff;

    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function view(User $user, User $managedUser): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, User $managedUser): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user, User $managedUser): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Shared admin CMS / content surfaces (admin + superadmin).
     */
    public function manageContent(User $user): bool
    {
        return $this->isAdminOrAbove($user);
    }

    /**
     * Staff-facing vendor (cleaner) management.
     */
    public function manageVendors(User $user): bool
    {
        return $this->isStaff($user);
    }

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
