<?php

namespace App\Models;

use Database\Factories\ChronologyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'startdate',
    'allregion',
    'allsubregion',
    'chronologyoption',
    'created_date',
    'update_date',
])]
class Chronology extends Model
{
    /** @use HasFactory<ChronologyFactory> */
    use HasFactory;

    protected $table = 'chronology';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'startdate' => 'date',
            'created_date' => 'datetime',
            'update_date' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ChronologyOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(ChronologyOrder::class, 'chronology_id');
    }

    /**
     * @return HasMany<ChronologyMail, $this>
     */
    public function mails(): HasMany
    {
        return $this->hasMany(ChronologyMail::class, 'chronology_id');
    }

    /**
     * @return BelongsToMany<Region, $this>
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'chronology_regions');
    }

    /**
     * @return BelongsToMany<Subregion, $this>
     */
    public function subregions(): BelongsToMany
    {
        return $this->belongsToMany(Subregion::class, 'chronology_subregions');
    }

    /**
     * @return HasMany<ChronologyOwnerEmail, $this>
     */
    public function ownerOptOuts(): HasMany
    {
        return $this->hasMany(ChronologyOwnerEmail::class, 'chronology_id');
    }
}
