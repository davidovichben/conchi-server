<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Media extends BaseModel
{
    use HasFactory;

    public static function createInstance($values, $inputFile)
    {
        $file = new UploadedFile($inputFile);

        $media = new self();
        $media->key_name = $values['key_name'];
        $media->screen = $values['screen'];
        $media->type = $values['type'] ?? 'image';
        $media->is_editable = 1;
        $media->path = 'media/' . $media->key_name . '.' . $file->ext;
        if ($media->save()) {
            $file->store('media/' . $media->key_name);
        }
    }

    public function updateInstance($inputFile)
    {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $file = new UploadedFile($inputFile);

        $path = 'media/' . $this->key_name . '.' . $file->ext;

        $file->store($path);

        $this->path = $path;
        $this->update();
    }

    public function deleteInstance() {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $this->delete();
    }
}
