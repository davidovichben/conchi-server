<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramDay extends BaseModel
{
    use HasFactory;

    public function userDays()
    {
        return $this->hasMany(UserProgramDay::class);
    }
}
