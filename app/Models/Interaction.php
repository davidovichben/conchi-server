<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Interaction extends BaseModel
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'category_id', 'sub_category_id', 'guidelines', 'show_order'];

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
        return $this->morphToMany(ProgramDay::class, 'program_day_activity');
    }

    public function userInteractions()
    {
        return $this->hasMany(UserInteraction::class);
    }

    public function selectAudioFile($userDetails)
    {
        if ($this->audioFiles->count() === 0) {
            return null;
        }

        // Test for child gender first

        $genderFilteredFiles = $this->audioFiles->where(function($audioFile) use ($userDetails) {
            return $userDetails->child_gender === $audioFile->gender;
        });

        // Test for parent status

        $audioFile = $genderFilteredFiles->first(function($audioFile) use ($userDetails) {
            $isCouple = $userDetails->family_status === 'married' || $userDetails->family_status === 'divorced';
            if ($isCouple && $audioFile->parents_status === 'couple') {
                return true;
            }

            if (!$isCouple) {
                $isFather = $userDetails->parent1_role === 'father' || $userDetails->parent2_role === 'father';
                if ($isFather && $audioFile->parents_status === 'single_male') {
                    return true;
                }

                $isMother = $userDetails->parent1_role === 'mother' || $userDetails->parent2_role === 'mother';
                if ($isMother && $audioFile->parents_status === 'single_female') {
                    return true;
                }
            }

            return false;
        });

        if ($audioFile) {
            return $audioFile;
        }

        if ($genderFilteredFiles->count() > 0) {
            return $genderFilteredFiles->first(function($audioFile) {
                return !$audioFile->parents_status;
            });
        }

        $selectedFile = $this->audioFiles->first(function($audioFile) {
            return !$audioFile->parents_status && !$audioFile->child_gender;
        });

        return $selectedFile ?? $this->audioFiles[0];
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
            Storage::delete($audioFile->file);
        });

        $audioFilesToDelete->delete();

        $this->fill($values);

        $this->update();

        DB::commit();
    }

    public function deleteInstance()
    {
        DB::beginTransaction();

        Storage::deleteDirectory('interactions/' . $this->id);

        ProgramDayActivity::where('program_day_activity_id', $this->id)->where('program_day_activity_type', 'App\Models\Interaction')->delete();

        $this->audioFiles()->delete();
        $this->delete();

        DB::commit();
    }

    public static function mapInteractions($interactions, $user, $prefixFiles, $displayCategories = true)
    {

        return $interactions->map(function($interaction) use ($user, $prefixFiles, $displayCategories) {
            $values = [
                ...$interaction->getAttributes(),
                'description'   => str_replace('{child_name}', $user->details->child_name, $interaction->description),
                'guidelines'    => str_replace('{child_name}', $user->details->child_name, $interaction->guidelines),
                'liked'         => $interaction->userInteractions->count() > 0,
                'status'        => $interaction->userInteractions->count() > 0 ? $interaction->userInteractions->first()->status : null,
                'category'      => $displayCategories && $interaction->category ? [
                    'id'    => $interaction->category->id,
                    'name'  => $interaction->category->name,
                    'image' => $interaction->category->image ? url(Storage::url($interaction->category->image)) : null
                ] : null,
                'subCategory'   => !$displayCategories && $interaction->subCategory ? [
                    'id'    => $interaction->subCategory->id,
                    'name'  => $interaction->subCategory->name,
                    'image' => $interaction->subCategory->image ? url(Storage::url($interaction->subCategory->image)) : null
                ] : null,
            ];

            if ($interaction->userInteractions->count() > 0) {
                $values['status'] = $interaction->userInteractions->first()->status;
                $values['liked'] = $interaction->userInteractions->first()->liked;
            }

            $audioFile = $interaction->selectAudioFile($user->details);
            if ($audioFile) {
                $values['name_prefix'] = $prefixFiles->count() > 0 ? $prefixFiles->random() : null;
                $values['audio'] = url(Storage::url($audioFile->file));
                $values['duration'] = $audioFile->duration;
            }

            return $values;
        });
    }
}
