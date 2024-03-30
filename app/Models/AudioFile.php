<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioFile extends Model
{
    use HasFactory;

    public static function createInstances($interactionId, $values)
    {
        $audioFiles = [];
        foreach ($values as $value) {
            $audioFiles[] = [
                'interaction_id'    => $interactionId,
                'file'              => self::uploadFile($value['file'], $interactionId),
                'gender'            => $value['gender'],
                'duration'          => $value['duration'],
                'parents_status'    => $value['parents_status']
            ];
        }

        self::insert($audioFiles);
    }

    public static function updateInstances($values, $interactionId)
    {
        $valuesWithFilesIds = $values->where(function($value) {
            return $value['file'];
        })->pluck('id');

        AudioFile::whereIn('id', $valuesWithFilesIds)->select('file')->get()->each(function($audioFile) {
            Storage::delete($audioFile->file);
        });

        $values->each(function ($value) use ($interactionId) {
            if ($value['file']) {
                $value['file'] = self::uploadFile($value['file'], $interactionId);
            } else {
                unset($value['file']);
                unset($value['duration']);
            }

            self::where('id', $value['id'])->update($value);
        });
    }

    public static function uploadFile($audio, $interactionId) {
        $path = 'interactions/' . $interactionId . '/' . Str::random(32);

        $file = new UploadedFile($audio);
        $file->store($path);

        return $path . '.' . $file->ext;
    }
}
