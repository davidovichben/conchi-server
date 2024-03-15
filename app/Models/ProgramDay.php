<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramDay extends BaseModel
{
    use HasFactory;

    public function week()
    {
        return $this->belongsTo(ProgramWeek::class);
    }

    public function userDays()
    {
        return $this->hasMany(UserProgramDay::class);
    }

    public function interactions()
    {
        return $this->belongsToMany(Interaction::class, 'interaction_days', 'day_id')->withPivot('period');
    }

    public function previousDay()
    {
        return ProgramDay::where('week_id', $this->week_id)->where('number', $this->number - 1)->first();
    }

    public function nextDay()
    {
        return ProgramDay::where('week_id', $this->week_id)->where('number', $this->number + 1)->first();
    }
}
