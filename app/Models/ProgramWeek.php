<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramWeek extends BaseModel
{
    use HasFactory;

    public function days()
    {
        return $this->hasMany(ProgramDay::class, 'week_id');
    }

    public function userWeeks()
    {
        return $this->hasMany(UserProgramWeek::class);
    }

    public function questions()
    {
        return $this->hasMany(ProgramReportQuestion::class);
    }
}
