<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'lat', 'lng'])]
class Country extends Model
{
    public $timestamps = false;

    /** @return HasMany<State, $this> */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
