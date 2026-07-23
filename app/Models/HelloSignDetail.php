<?php

namespace App\Models;

use Database\Factories\HelloSignDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Signature request rows (table retained as hellosigndetails; live path is Zoho — ADR-013).
 */
#[Fillable([
    'files_url',
    'signing_url',
    'details_url',
    'signature_id',
    'ownerid_id',
    'ownerid_email',
    'filename',
    'chronology_id',
    'chronologyorder_id',
    'is_opened',
    'zoho_request_name',
    'zoho_document_id',
    'zoho_zsdocumentid',
    'zoho_template_ids',
    'zoho_request_id',
    'zoho_sign_status',
    'created_date',
    'update_date',
])]
class HelloSignDetail extends Model
{
    /** @use HasFactory<HelloSignDetailFactory> */
    use HasFactory;

    protected $table = 'hellosigndetails';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
            'update_date' => 'datetime',
        ];
    }

    public function isCompleted(): bool
    {
        return (int) $this->zoho_sign_status === 1;
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'ownerid_id');
    }
}
