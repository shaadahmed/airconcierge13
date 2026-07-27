<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'parent_id',
    'name',
    'path',
    'description',
    'link',
    'external_link',
    'order',
    'icon_class',
    'deleted',
])]
class Resource extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'link' => 'boolean',
            'external_link' => 'boolean',
            'order' => 'integer',
            'deleted' => 'boolean',
        ];
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->notDeleted()
            ->orderBy('order');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where('deleted', false)->orWhereNull('deleted');
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->where('parent_id', 0)->notDeleted()->orderBy('order');
    }

    public function spaPath(): ?string
    {
        $path = trim((string) $this->path);

        if ($path === '' || $path === '#') {
            return null;
        }

        return '/'.ltrim($path, '/');
    }
}
