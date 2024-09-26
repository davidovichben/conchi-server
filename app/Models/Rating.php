<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Rating extends BaseModel
{
    use HasFactory;

    protected $fillable = ['type', 'score', 'content', 'author'];

    public static function createInstance($values, $inputFile)
    {
        $file = new UploadedFile($inputFile);

        $rating = new self();
        $rating->fill($values);

        if ($rating->save()) {
            $rating->path = $rating->getBasePath() . '.' . $file->ext;
            $rating->update();

            $file->store($rating->getBasePath());
        }

        return $rating;
    }

    public function updateInstance($values, $inputFile)
    {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $file = new UploadedFile($inputFile);
        $file->store($this->getBasePath());

        $this->fill($values);
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
        return 'ratings/' . $this->id;
    }
}
