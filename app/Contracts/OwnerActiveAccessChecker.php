<?php

namespace App\Contracts;

use App\Models\User;

interface OwnerActiveAccessChecker
{
    /**
     * Whether a Property Owner has at least one active property.
     *
     * Business rule (Phase 1 scaffold): owners without active properties are limited to
     * statements / logout / terms routes. Real data check arrives with the properties module.
     *
     * TODO: Query properties via user↔owner ownership when schema and models exist.
     */
    public function hasActiveAccess(User $user): bool;
}
