<?php

namespace App\Models;

use Database\Factories\ChronologyMailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chronology_id',
    'order_id',
    'chronologyorder',
    'owner_id',
    'created_on',
    'update_on',
    'is_opened',
])]
class ChronologyMail extends Model
{
    /** @use HasFactory<ChronologyMailFactory> */
    use HasFactory;

    protected $table = 'chronology_mail';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_on' => 'datetime',
            'update_on' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Chronology, $this>
     */
    public function chronology(): BelongsTo
    {
        return $this->belongsTo(Chronology::class, 'chronology_id');
    }

    /**
     * @return BelongsTo<ChronologyOrder, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ChronologyOrder::class, 'order_id');
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function isOpened(): bool
    {
        return (string) $this->is_opened === '1';
    }

    public function markOpened(): void
    {
        $this->update([
            'is_opened' => '1',
            'update_on' => now(),
        ]);
    }
}
