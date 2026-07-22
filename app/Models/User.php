<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Whether this owner has agreed to terms.
     *
     * Phase 1 scaffold: always true until owner_terms_agreements (or equivalent) lands.
     */
    public function hasAgreedToTerms(): bool
    {
        return true;
    }

    /**
     * Whether this owner has at least one active property.
     *
     * Phase 1 scaffold: always true until the properties domain can supply the real check.
     * Business meaning (later): derived from property status — not users.active / owners.status.
     */
    public function hasActiveAccess(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
