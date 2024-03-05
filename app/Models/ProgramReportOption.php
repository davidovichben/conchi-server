<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramReportOption extends Model
{
    use HasFactory;

    protected $hidden = ['program_report_question_id', 'created_at', 'updated_at'];

}
