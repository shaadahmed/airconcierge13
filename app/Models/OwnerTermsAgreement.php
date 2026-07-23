<?php

namespace App\Models;

use Database\Factories\OwnerTermsAgreementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'agreed_terms'])]
class OwnerTermsAgreement extends Model
{
    /** @use HasFactory<OwnerTermsAgreementFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'agreed_terms' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasAgreed(): bool
    {
        return $this->agreed_terms === true;
    }
}
