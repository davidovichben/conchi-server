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
        $media->path = $media->getBasePath() . '.' . $file->ext;
        if ($media->save()) {
            $file->store($media->getBasePath());
        }
    }

    public function updateInstance($inputFile)
    {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $file = new UploadedFile($inputFile);
        $file->store($this->getBasePath());

        $this->path = $this->getBasePath() . '.' . $file->ext;
        $this->update();
    }

    public function deleteInstance() {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $this->delete();
    }

    private function getBasePath() {
        return 'media/' . $this->key_name;
    }
}
