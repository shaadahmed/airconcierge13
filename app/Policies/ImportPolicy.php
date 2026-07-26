<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ImportedEmail;
use App\Models\User;

class ImportPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, ImportedEmail $importedEmail): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, ImportedEmail $importedEmail): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, ImportedEmail $importedEmail): bool
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
