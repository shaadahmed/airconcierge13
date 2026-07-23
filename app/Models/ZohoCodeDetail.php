<?php

namespace App\Models;

use Database\Factories\ZohoCodeDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'zoho_code',
    'zoho_access_token',
    'zoho_refresh_token',
    'created_dtm',
    'update_dtm',
    'is_deleted',
])]
class ZohoCodeDetail extends Model
{
    /** @use HasFactory<ZohoCodeDetailFactory> */
    use HasFactory;

    protected $table = 'zoho_code_details';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_dtm' => 'datetime',
            'update_dtm' => 'datetime',
        ];
    }

    public function isExpired(int $graceMinutes = 50): bool
    {
        if ($this->update_dtm === null) {
            return true;
        }

        return $this->update_dtm->lte(now()->subMinutes($graceMinutes));
    }
}
