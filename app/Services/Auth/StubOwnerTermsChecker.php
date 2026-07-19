<?php

namespace App\Services\Auth;

use App\Contracts\OwnerTermsChecker;
use App\Models\User;

/**
 * Phase 1 stub: always treat terms as agreed until the terms domain is implemented.
 */
final class StubOwnerTermsChecker implements OwnerTermsChecker
{
    public function hasAgreed(User $user): bool
    {
        return true;
    }
}
