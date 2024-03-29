<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProgramWeek extends BaseModel
{
    use HasFactory;

    protected $fillable = ['status', 'week', 'program_id', 'user_id'];

    public static function saveInstance($values)
    {
        $instance = new self();
        $instance->fill($values);
        $instance->save();
        return $instance;
    }
}
