<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:d/m/Y',
    ];

    protected $hidden = ['updated_at'];
}
