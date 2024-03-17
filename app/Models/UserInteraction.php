<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserInteraction extends BaseModel
{
    use HasFactory;

    public function interaction()
    {
        return $this->belongsTo(Interaction::class);
    }
}
