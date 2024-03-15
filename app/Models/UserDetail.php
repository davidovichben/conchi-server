<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDetail extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'child_has_nickname'    => 'boolean',
        'child_birth_date'      => 'datetime:d/m/Y'
    ];
}
