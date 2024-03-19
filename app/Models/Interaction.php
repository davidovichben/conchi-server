<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Interaction extends BaseModel
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'category_id', 'sub_category_id', 'guidelines', 'show_order'];

    protected $casts = [
        'guidelines'    => 'json'
    ];

    public function audioFiles()
    {
        return $this->hasMany(AudioFile::class);
    }

    public function category()
    {
        return $this->belongsTo(InteractionCategory::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(InteractionSubCategory::class, 'sub_category_id');
    }

    public function days()
    {
        return $this->belongsToMany(ProgramDay::class, 'interaction_days', 'interaction_id', 'day_id');
    }

    public function userInteractions()
    {
        return $this->hasMany(UserInteraction::class);
    }

    public static function getAudioFile($interaction)
    {
        $file = 'sample.mp3';

        return [
            'file'      => 'data:audio/webm;codecs=opus;base64,' . base64_encode(Storage::get($file)),
            'duration'  => 19
        ];
    }

    public static function createInstance($values)
    {
        DB::beginTransaction();

        $interaction = new self();
        $interaction->fill($values);

        $interaction->save();

        AudioFile::createInstances($interaction->id, $values['audio_files']);

        DB::commit();
    }

    public function updateInstance($values)
    {
        DB::beginTransaction();

        $audioFiles = collect($values['audio_files']);

        // Insert files

        [$newAudioFiles, $audioFilesToUpdate] = $audioFiles->partition(function ($value) {
            return !$value['id'];
        });

        AudioFile::createInstances($this->id, $newAudioFiles->toArray());

        // Update files

        AudioFile::updateInstances($audioFilesToUpdate, $this->id);

        // Delete files

        $audioFilesToDelete = $this->audioFiles()->whereNotIn('id', $audioFiles->pluck('id'));

        $audioFilesToDelete->get()->each(function($audioFile) {
            Storage::delete('public/' . $audioFile->file);
        });

        $audioFilesToDelete->delete();

        $this->fill($values);
        $this->update();

        DB::commit();
    }

    public function deleteInstance()
    {
        DB::beginTransaction();

        Storage::deleteDirectory('public/interactions/' . $this->id);

        $this->audioFiles()->delete();
        $this->delete();

        DB::commit();
    }

    public static function getQuery()
    {
        return DB::table('interactions', 'i')
            ->join('interaction_categories as ic', 'ic.id', 'i.category_id')
            ->leftJoin('user_interactions as ui', function($query) {
                return $query->on('interaction_id', 'i.id')->where('user_id', Auth::id());
            })
            ->selectRaw('i.id, ui.liked, ui.status, ic.name as category, i.guidelines, i.period, i.duration, i.title, i.description');
    }

    public static function getInteractions($rows)
    {
        return $rows->map(function($interaction) {
            $file = 'sample.mp3';

            return [
                ...(array)$interaction,
                'guidelines'    => json_decode($interaction->guidelines),
                'audio'         => [
                    'file'      => 'data:audio/webm;codecs=opus;base64,' . base64_encode(Storage::get($file)),
                    'duration'  => 19
                ]
            ];
        });
    }
}
