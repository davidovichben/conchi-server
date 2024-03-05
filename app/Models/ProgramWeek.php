<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramWeek extends BaseModel
{
    use HasFactory;
    public function userWeeks()
    {
        return $this->hasMany(UserProgramWeek::class);
    }
}
