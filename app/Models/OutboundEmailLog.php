<?php

namespace App\Models;

use Database\Factories\OutboundEmailLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'status',
    'subject',
    'body',
    'to_addresses',
    'cc_addresses',
    'bcc_addresses',
    'from_email',
    'from_name',
    'reply_to',
    'error_message',
    'attempt_count',
    'last_attempt_by',
    'source',
    'metadata',
    'sent_at',
])]
class OutboundEmailLog extends Model
{
    /** @use HasFactory<OutboundEmailLogFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return HasMany<OutboundEmailAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(OutboundEmailAttachment::class);
    }

    public function markSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $message,
        ]);
    }
}
