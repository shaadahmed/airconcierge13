<?php

namespace App\Models;

use Database\Factories\ChronologyOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chronology_id',
    'day',
    'time',
    'minute',
    'previousaction',
    'document_id',
    'template_id',
    'document_name',
    'template_name',
    'created_date',
    'update_date',
])]
class ChronologyOrder extends Model
{
    /** @use HasFactory<ChronologyOrderFactory> */
    use HasFactory;

    protected $table = 'chronologyorder';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
            'update_date' => 'datetime',
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
     * @return BelongsTo<DocumentUpload, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentUpload::class, 'document_id');
    }

    /**
     * @return BelongsTo<EmailTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function requiresPriorOpen(): bool
    {
        return (string) $this->previousaction !== '0' && $this->previousaction !== null && $this->previousaction !== '';
    }
}
