<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InteractionSubCategory extends BaseModel
{
    protected $fillable = ['interaction_category_id', 'name'];

    public function interactionCategory()
    {
        return $this->belongsTo(InteractionCategory::class);
    }

    public static function createInstance($values)
    {
        $subCategory = new self();
        $subCategory->fill($values);

        if ($values['image']) {
            $subCategory->uploadImage($values['image']);
        }

        $subCategory->save();

        return $subCategory;
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

    public function deleteInstance()
    {
        $this->deleteImage();
        $this->delete();
    }

    public function uploadImage($image) {
        $path = 'sub-categories/' . Str::random(32);

        $file = new UploadedFile($image);
        $file->store($path);

        $this->image = $path . '.' . $file->ext;
    }

    public function deleteImage() {
        if (Storage::exists($this->image)) {
            Storage::delete($this->image);
        }

        $this->image = null;
    }
}
