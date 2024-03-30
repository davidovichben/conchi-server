<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramReportOption extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'interaction_id'];

    protected $hidden = ['program_report_question_id', 'created_at', 'updated_at'];

    public function interaction()
    {
        return $this->belongsTo(Interaction::class);
    }


    public static function createInstance($values)
    {
        $option = new self;
        $option->program_report_question_id = $values['questionId'];
        $option->fill($values);
        $option->save();

        return $option;
    }

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();

        return $this;
    }
}
