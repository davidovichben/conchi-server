<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProgramDay extends BaseModel
{
    use HasFactory;

    protected $fillable = ['program_day_id', 'user_id', 'completed'];

    protected $casts = [
        'completed' => 'boolean'
    ];

    public static function createInstance($values)
    {
        $userProgramDay = new self();
        $userProgramDay->fill($values);
        $userProgramDay->save();
    }

}
