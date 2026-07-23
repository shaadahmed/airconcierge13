<?php

namespace App\Models;

use Database\Factories\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
    'templatesubject',
    'acknowledgementsubject',
    'acknowledgement',
    'acknowledgementdescription',
    'description',
    'allregion',
    'allsubregion',
    'createdate',
])]
class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use HasFactory;

    protected $table = 'template';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'createdate' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Region, $this>
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'template_regions', 'template_id', 'region_id');
    }

    /**
     * @return BelongsToMany<Subregion, $this>
     */
    public function subregions(): BelongsToMany
    {
        return $this->belongsToMany(Subregion::class, 'template_subregions', 'template_id', 'subregion_id');
    }
}
