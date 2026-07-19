<?php

namespace App\Contracts;

use App\Models\User;

interface OwnerTermsChecker
{
    /**
     * Whether a Property Owner has agreed to terms.
     *
     * TODO: Implement against owner_terms_agreements (or equivalent) when that domain lands.
     */
    public function hasAgreed(User $user): bool;
}
