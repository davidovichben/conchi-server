<?php

namespace App\Models;

class Hobby extends BaseModel
{
    protected $fillable = ['name'];

    public static function createInstance($values)
    {
        $hobby = new self();
        $hobby->fill($values);
        $hobby->save();

        return $hobby;
    }

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();
    }
}
