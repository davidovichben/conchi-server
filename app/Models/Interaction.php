<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Interaction extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'guidelines'    => 'json'
    ];

    protected $hidden = ['id', 'category_id', 'created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(InteractionCategory::class, 'category_id');
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

    public static function getQuery($userId)
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
