<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramReportOption extends Model
{
    use HasFactory;

    protected $fillable = ['content'];

    protected $hidden = ['program_report_question_id', 'created_at', 'updated_at'];

    public static function createInstance($values)
    {
        $option = new self;
        $option->program_report_question_id = $values['questionId'];
        $option->content = $values['content'];
        $option->save();

        return $option;
    }
}
