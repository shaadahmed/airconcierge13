<?php

namespace App\Models;

use Database\Factories\MailSendFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'owner_id',
    'properties_id',
    'document_id',
    'template_id',
    'documentname',
    'files_url',
    'signing_url',
    'details_url',
    'signature_id',
    'is_opened',
    'created_date',
    'update_date',
])]
class MailSend extends Model
{
    /** @use HasFactory<MailSendFactory> */
    use HasFactory;

    protected $table = 'tbl_mailsend';

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

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}
