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

    protected $fillable = ['title', 'description', 'category_id', 'guidelines'];

    protected $casts = [
        'guidelines'    => 'json'
    ];

    public function category()
    {
        return $this->belongsTo(InteractionCategory::class, 'category_id');
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
        $interaction = new self();
        $interaction->fill($values);

        if ($values['audio']) {
            $interaction->uploadFile($values['audio']);
        }

        $interaction->save();
    }

    public function updateInstance($values)
    {
        if ($values['audio']) {
            $this->deleteAudio();
            $this->uploadFile($values['audio']);
        }

        $this->fill($values);
        $this->update();
    }

    public function uploadFile($audio) {
        $path = 'interactions/' . Str::random(32);

        $file = new UploadedFile($audio);
        $file->store('public/' . $path);

        $this->audio = $path . '.' . $file->ext;
    }

    public function deleteInstance()
    {
        $this->deleteAudio();
        $this->delete();
    }

    public function deleteAudio() {
        Storage::delete('public/' . $this->audio);
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
