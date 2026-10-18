<?php

namespace App\Policies;

use App\Models\Subregion;
use App\Models\User;
use App\Policies\Concerns\AuthorizesStaff;

/**
 * Subregions admin CRUD (legacy: superadmin|admin|Regional Manager).
 */
class SubregionPolicy
{
    use AuthorizesStaff;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Subregion $subregion): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Subregion $subregion): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Subregion $subregion): bool
    {
        return $this->isStaff($user);
    }
}
