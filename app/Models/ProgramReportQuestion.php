<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramReportQuestion extends Model
{
    use HasFactory;

    protected $hidden = ['program_week_id', 'created_at', 'updated_at'];

    public function options()
    {
        return $this->hasMany(ProgramReportOption::class, 'program_report_question_id');
    }

    public function userOption()
    {
        return $this->hasOne(UserProgramReport::class, 'program_report_question_id');
    }
}
