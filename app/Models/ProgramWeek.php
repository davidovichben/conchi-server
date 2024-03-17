<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class ProgramWeek extends BaseModel
{
    use HasFactory;

    protected $fillable = ['description'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

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

    public static function createInstance($values)
    {
        DB::beginTransaction();

        $lastWeek = self::lastWeek();

        $week = new self();
        $week->description = $values['description'];
        $week->number = $lastWeek->number + 1;
        $week->save();

        $values = [];
        for ($i = 1; $i <= 7; $i++) {
            $values[] = [
                'week_id'   => $week->id,
                'number'    => $i,
            ];
        }

        ProgramDay::insert($values);

        DB::commit();

        return $week;
    }

    public static function lastWeek()
    {
        return ProgramWeek::orderBy('number', 'desc')->limit(1)->first();
    }
}
