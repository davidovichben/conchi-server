<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'content'];

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();

        return $this;
    }
}
