<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source', 'subject', 'body', 'from_email', 'status'])]
class ImportedEmail extends Model
{
    use HasFactory;
}
