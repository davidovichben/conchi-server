<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgramReport extends Model
{
    use HasFactory;

    protected $hidden = ['id', 'user_id', 'program_report_question_id', 'created_at', 'updated_at'];

}
