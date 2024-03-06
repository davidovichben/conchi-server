<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InteractionCategory extends BaseModel
{
    use HasFactory;

    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'category_id');
    }
}
