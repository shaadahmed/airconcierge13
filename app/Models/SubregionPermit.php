<?php

namespace App\Models;

use Database\Factories\SubregionPermitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'subregion_id',
    'business_license_account',
    'permit_no',
    'permit_issue_date',
    'permit_expiry_date',
    'permit_length',
])]

class SubregionPermit extends Model
{
    /** @use HasFactory<SubregionPermitFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Subregion, $this>
     */
    public function subregion(): BelongsTo
    {
        return $this->belongsTo(Subregion::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'business_license_account' => 'integer',
            'permit_issue_date' => 'date',
            'permit_expiry_date' => 'date',
            'permit_length' => 'integer',
        ];
    }
}
