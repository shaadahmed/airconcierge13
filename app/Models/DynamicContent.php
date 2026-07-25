<?php

namespace App\Models;

use Database\Factories\DynamicContentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['page_id', 'content'])]
class DynamicContent extends Model
{
    /** @use HasFactory<DynamicContentFactory> */
    use HasFactory;

    protected $table = 'dynamic_content';

    public const OWNER_AGREEMENT_PAGE_ID = 'owner-agreement';

    public function decodedContent(): string
    {
        return html_entity_decode($this->content ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
