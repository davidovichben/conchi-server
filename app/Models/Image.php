<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Image extends BaseModel
{
    use HasFactory;

    protected $fillable = ['key_name', 'screen'];

    public static function createInstance($values, $inputFile)
    {
        $file = new UploadedFile($inputFile);

        $image = new self();
        $image->key_name = $values['key_name'];
        $image->screen = $values['screen'] ?? null;
        $image->is_editable = 1;
        $image->path = 'images/' . $image->key_name . '.' . $file->ext;
        if ($image->save()) {
            $file->store('images/' . $image->key_name);
        }
    }

    public function updateInstance($inputFile)
    {
        Storage::delete($this->path);

        $file = new UploadedFile($inputFile);
        $file->store('images/' . $this->key_name);

        $this->path = 'images/' . $this->key_name . '.' . $file->ext;
        $this->update();
    }

    public function deleteInstance() {
        Storage::delete($this->path);
        $this->delete();
    }
}
