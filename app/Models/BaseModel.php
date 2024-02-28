<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $hidden = ['user_id', 'created_at', 'updated_at'];

    protected $guarded = ['user_id', 'created_at', 'updated_at'];
}
