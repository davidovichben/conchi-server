<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Image extends BaseModel
{
    use HasFactory;

    public function updateInstance($inputFile)
    {
        Storage::delete('public/' . $this->path);

        $file = new UploadedFile($inputFile);
        $file->store('public/images/' . $this->key_name);

        $this->path = 'images/' . $this->key_name . '.' . $file->ext;
        $this->update();
    }
}
