<?php

namespace App\Models;

use Database\Factories\OwnerEmailNotificationLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'owner_id', 'email_type', 'status'])]
class OwnerEmailNotificationLog extends Model
{
    /** @use HasFactory<OwnerEmailNotificationLogFactory> */
    use HasFactory;

    protected $table = 'owners_emails_notifications_log';

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }
}
