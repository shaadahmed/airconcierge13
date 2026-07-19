<?php

namespace App\Services\Auth;

use App\Contracts\OwnerActiveAccessChecker;
use App\Models\User;

/**
 * Phase 1 stub: always allow access until the properties domain can supply real checks.
 */
final class StubOwnerActiveAccessChecker implements OwnerActiveAccessChecker
{
    public function hasActiveAccess(User $user): bool
    {
        return true;
    }
}
