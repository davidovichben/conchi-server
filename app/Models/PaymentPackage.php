<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPackage extends BaseModel
{
    use HasFactory;

    protected $fillable = ['title', 'price', 'perks', 'guidelines', 'contents'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function createInstance($values)
    {
        $paymentPackage = new self();
        $paymentPackage->fill($values);
        $paymentPackage->save();

        return $paymentPackage;
    }

    public function updateInstance($values)
    {
        $this->fill($values);
        $this->update();
    }
}
