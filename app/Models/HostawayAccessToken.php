<?php

namespace App\Models;

use Database\Factories\HostawayAccessTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $expiry
 */
#[Fillable(['access_token', 'token_type', 'expiry'])]
class HostawayAccessToken extends Model
{
    /** @use HasFactory<HostawayAccessTokenFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiry' => 'date',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expiry === null || $this->expiry->lte(now()->startOfDay());
    }
}
