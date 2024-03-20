<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InteractionCategory extends BaseModel
{
    protected $fillable = ['name', 'description'];

    use HasFactory;

    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'category_id');
    }

    public function subCategories()
    {
        return $this->hasMany(InteractionSubCategory::class);
    }

    public static function createInstance($values)
    {
        $category = new self();
        $category->fill($values);

        if ($values['image']) {
           $category->uploadImage($values['image']);
        }

        $category->save();
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
        DB::beginTransaction();

        ProgramDayActivity::where('activity_id', $this->id)->where('activity_type', 'App\Models\InteractionCategory')->delete();

        $this->deleteImage();
        $this->delete();

        DB::commit();
    }

    public function uploadImage($image) {
        $path = 'categories/' . Str::random(32);

        $file = new UploadedFile($image);
        $file->store('public/' . $path);

        $this->image = $path . '.' . $file->ext;
    }

    public function deleteImage() {
        Storage::delete('public/' . $this->image);
        $this->image = null;
    }
}
