<?php

namespace App\Models;

class InteractionSubCategory extends BaseModel
{
    protected $fillable = ['interaction_category_id', 'name'];

    public function interactionCategory()
    {
        return $this->belongsTo(InteractionCategory::class);
    }

    public static function createInstance($values)
    {
        $subCategory = new self();
        $subCategory->fill($values);
        $subCategory->save();

        return $subCategory;
    }

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();
    }
}
