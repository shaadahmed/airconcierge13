<?php

namespace App\Models;

use Database\Factories\OutboundEmailAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'outbound_email_log_id',
    'original_filename',
    'mime_type',
    'storage_type',
    'disk_path',
    'size_bytes',
    'content_hash',
])]
class OutboundEmailAttachment extends Model
{
    /** @use HasFactory<OutboundEmailAttachmentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<OutboundEmailLog, $this>
     */
    public function outboundEmailLog(): BelongsTo
    {
        return $this->belongsTo(OutboundEmailLog::class);
    }
}
