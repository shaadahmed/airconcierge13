<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'first_name', 'last_name', 'phone', 'email', 'regional_manager', 'is_active',
    'compensation_structure', 'home_address', 'employment_status', 'salary_type',
    'deleted', 'region', 'city', 'state', 'zip',
])]
class Manager extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'regional_manager' => 'boolean',
            'is_active' => 'boolean',
            'deleted' => 'boolean',
        ];
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where('deleted', false)->orWhereNull('deleted');
        });
    }

    public function displayName(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }
}
