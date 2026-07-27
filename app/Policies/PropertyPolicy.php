<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Policies\Concerns\AuthorizesStaff;

class PropertyPolicy
{
    use AuthorizesStaff;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Property $property): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->isStaff($user);
    }

}
