<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Rating extends BaseModel
{
    use HasFactory;

    protected $fillable = ['type', 'score', 'content', 'author'];

    public static function createInstance($values, $inputFile = null)
    {
        $rating = new self();
        $rating->fill($values);

        if ($rating->save()) {
            if ($inputFile) {
                $file = new UploadedFile($inputFile);
                $file->store($rating->getBasePath());

                $rating->path = $rating->getBasePath() . '.' . $file->ext;
            }

            $rating->update();
        }

        return $rating;
    }

    public function updateInstance($values, $inputFile = null)
    {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        if ($inputFile) {
            $file = new UploadedFile($inputFile);
            $file->store($this->getBasePath());
        }


        $this->fill($values);
        $this->path = $inputFile ? $this->getBasePath() . '.' . $file->ext : null;
        $this->update();
    }

    public function deleteInstance() {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $this->delete();
    }

    private function getBasePath() {
        return 'ratings/' . $this->id;
    }
}
