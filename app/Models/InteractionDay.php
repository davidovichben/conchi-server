<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteractionDay extends Model
{
    use HasFactory;

    public static function createInstance($dayId, $values)
    {
        $interactionDay = new self();
        $interactionDay->day_id = $dayId;
        $interactionDay->interaction_id = $values['interactionId'];
        $interactionDay->period = $values['period'];
        $interactionDay->save();
    }
}
