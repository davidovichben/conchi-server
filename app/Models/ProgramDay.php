<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    public function interactions(): MorphToMany
    {
        return $this->morphedByMany(Interaction::class, 'program_day_activity')->withPivot('period');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(InteractionCategory::class, 'program_day_activity')->withPivot('period');
    }

    public static function createInstance($weekId)
    {
        $lastDay = self::lastDayInWeek($weekId);

        $day = new self();
        $day->week_id = $weekId;
        $day->number = $lastDay->number + 1;
        $day->save();

        return ProgramDay::find($day->id);
    }

    public function previousDay()
    {
        return ProgramDay::where('week_id', $this->week_id)->where('number', $this->number - 1)->first();
    }

    public function nextDay()
    {
        return ProgramDay::where('week_id', $this->week_id)->where('number', $this->number + 1)->first();
    }

    public static function lastDayInWeek($weekId)
    {
        return ProgramDay::where('week_id', $weekId)->orderBy('number', 'desc')->limit(1)->first();
    }
}
