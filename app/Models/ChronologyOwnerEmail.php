<?php

namespace App\Models;

use Database\Factories\ChronologyOwnerEmailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chronology_id',
    'owner_id',
    'owner_email',
    'status',
])]
class ChronologyOwnerEmail extends Model
{
    /** @use HasFactory<ChronologyOwnerEmailFactory> */
    use HasFactory;

    protected $table = 'tbl_chronologyowneremail';

    /**
     * @return BelongsTo<Chronology, $this>
     */
    public function chronology(): BelongsTo
    {
        return $this->belongsTo(Chronology::class, 'chronology_id');
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}
