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
        Storage::delete($this->path);

        $file = new UploadedFile($inputFile);
        $file->store('media/' . $this->key_name);

        $this->path = 'media/' . $this->key_name . '.' . $file->ext;
        $this->update();
    }

    public function deleteInstance() {
        Storage::delete($this->path);
        $this->delete();
    }
}
