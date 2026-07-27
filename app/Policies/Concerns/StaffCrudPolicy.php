<?php

namespace App\Policies\Concerns;

use App\Models\User;

abstract class StaffCrudPolicy
{
    use AuthorizesStaff;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->isStaff($user);
    }
}
