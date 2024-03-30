<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        if ($values['image']) {
            $article->uploadImage($values['image']);
        }

        $article->save();

        return $article;
    }

    public function updateInstance($values)
    {
        if ($values['image']) {
            $this->deleteImage();
            $this->uploadImage($values['image']);
        } else if ($values['deleteImage']) {
            $this->deleteImage();
        }

        $this->fill($values);
        $this->update();
    }

    public function uploadImage($image) {
        $path = 'articles/' . Str::random(32);

        $file = new UploadedFile($image);
        $file->store($path);

        $this->image = $path . '.' . $file->ext;
    }

    public function deleteImage() {
        Storage::delete($this->image);
        $this->image = null;
    }

    public function deleteInstance()
    {
        $this->deleteImage();
        $this->delete();
    }
}
