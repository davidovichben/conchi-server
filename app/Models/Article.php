<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:d/m/Y',
    ];

    protected $hidden = ['updated_at'];

    protected $fillable = ['title', 'description', 'content', 'position'];

    public static function createInstance($values)
    {
        $article = new self();
        $article->fill($values);
        $article->save();

        return $article;
    }

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();
    }
}
