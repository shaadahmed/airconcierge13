<?php

namespace App\Models;

use Database\Factories\DocumentUploadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
    'ownerspecific',
    'document',
    'roletitle',
    'nextstepinstruction',
    'type',
    'signid',
    'zohoactionid',
    'dropbox_form_name',
    'allregion',
    'allsubregion',
    'createdate',
])]
class DocumentUpload extends Model
{
    /** @use HasFactory<DocumentUploadFactory> */
    use HasFactory;

    protected $table = 'document_uploads';

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

    public function requiresZohoSign(): bool
    {
        return filled($this->signid);
    }

    /**
     * @return BelongsToMany<Region, $this>
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'document_regions', 'document_id', 'region_id');
    }

    /**
     * @return BelongsToMany<Subregion, $this>
     */
    public function subregions(): BelongsToMany
    {
        return $this->belongsToMany(Subregion::class, 'document_subregions', 'document_id', 'subregion_id');
    }

    /**
     * @return BelongsToMany<Owner, $this>
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class, 'document_owner', 'document_id', 'owner_id');
    }
}
