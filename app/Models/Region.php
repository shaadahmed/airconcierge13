<?php

namespace App\Models;

use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'code', 'color'])]

class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return HasMany<Subregion, $this>
     */
    public function subregions(): HasMany
    {
        return $this->hasMany(Subregion::class);
    }
}
