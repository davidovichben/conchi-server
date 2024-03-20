<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramDayActivity extends Model
{
    use HasFactory;

    public static function upsertInstance($dayId, $values)
    {
        $values['program_day_activity_type'] = $values['program_day_activity_type'] === 'interaction' ? 'App\Models\Interaction' : 'App\Models\InteractionCategory';

        self::upsert([...$values, 'program_day_id' =>  $dayId],
            uniqueBy: ['period', 'program_day_id'],
            update: ['program_day_activity_id', 'program_day_activity_type']);
    }
}
