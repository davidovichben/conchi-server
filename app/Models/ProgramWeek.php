<?php

namespace App\Models;

use App\Services\UploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramWeek extends BaseModel
{
    use HasFactory;

    protected $fillable = ['description'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function days()
    {
        return $this->hasMany(ProgramDay::class, 'week_id');
    }

    public function userWeeks()
    {
        return $this->hasMany(UserProgramWeek::class);
    }

    public function questions()
    {
        return $this->hasMany(ProgramReportQuestion::class);
    }

    public static function createInstance($values)
    {
        DB::beginTransaction();

        $lastWeek = self::lastWeek();

        $week = new self();
        $week->description = $values->get('description');
        $week->number = $lastWeek->number + 1;

        if ($values->get('image')) {
            $week->uploadImage($values->get('image'));
        }

        $week->save();

        $values = [];
        for ($i = 1; $i <= 7; $i++) {
            $values[] = [
                'week_id'   => $week->id,
                'number'    => $i,
            ];
        }

        ProgramDay::insert($values);

        DB::commit();

        return $week;
    }

    public function updateInstance(Collection $values)
    {
        if ($values->get('image')) {
            $this->deleteImage();
            $this->uploadImage($values->get('image'));
        } else if ($values->get('deleteImage')) {
            $this->deleteImage();
        }

        $this->description = $values->get('description');
        $this->update();
    }

    public function uploadImage($image) {
        $path = 'weeks/' . Str::random(32);

        $file = new UploadedFile($image);
        $file->store('public/' . $path);

        $this->image = $path . '.' . $file->ext;
    }

    public function deleteImage() {
        Storage::delete('public/' . $this->image);
        $this->image = null;
    }

    public static function lastWeek()
    {
        return ProgramWeek::orderBy('number', 'desc')->limit(1)->first();
    }
}
