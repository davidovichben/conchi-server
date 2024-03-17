<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPackage extends BaseModel
{
    use HasFactory;

    protected $fillable = ['title', 'price', 'description'];

    public static function createInstance($values)
    {
        $contentPackage = new self();
        $contentPackage->fill($values);
        $contentPackage->save();

        return $contentPackage;
    }

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();
    }
}
