<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
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

    private function isStaff(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Manager => true,
            default => false,
        };
    }
}
