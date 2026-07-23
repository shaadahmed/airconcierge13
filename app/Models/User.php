<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Whether this owner has agreed to terms (ADR-009 / ADR-012).
     */
    public function hasAgreedToTerms(): bool
    {
        if ($this->role !== UserRole::Owner) {
            return true;
        }

        return $this->ownerTermsAgreement?->hasAgreed() ?? false;
    }

    /**
     * Whether this owner has at least one qualifying live property (ADR-009 / ADR-012).
     *
     * Derived from properties.status — never from users.active or owners.status.
     */
    public function hasActiveAccess(): bool
    {
        if ($this->role !== UserRole::Owner) {
            return true;
        }

        return $this->owners()
            ->whereHas('properties', fn ($query) => $query->live())
            ->exists();
    }

    /**
     * @return HasOne<OwnerTermsAgreement, $this>
     */
    public function ownerTermsAgreement(): HasOne
    {
        return $this->hasOne(OwnerTermsAgreement::class);
    }

    /**
     * @return BelongsToMany<Owner, $this>
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class, 'user_owners');
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
            'active' => 'boolean',
        ];
    }
}
