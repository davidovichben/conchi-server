<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends BaseModel
{
    use HasFactory;

    protected $fillable = ['type', 'score', 'content'];

    public static function createInstance($values)
    {
        $rating = new self();
        $rating->fill($values);
        $rating->save();

        return $rating;
    }

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();
    }
}
