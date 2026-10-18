<?php

namespace App\Policies;

use App\Models\Country;
use App\Models\User;
use App\Policies\Concerns\AuthorizesStaff;

/**
 * Countries / states / cities admin CRUD (legacy: superadmin|admin|Regional Manager).
 */
class CountryPolicy
{
    use AuthorizesStaff;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Country $country): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Country $country): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Country $country): bool
    {
        return $this->isStaff($user);
    }
}
