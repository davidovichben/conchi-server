<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends BaseModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'payment_package_id', 'coupon_id', 'date','amount'];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the PaymentPackage model
    public function paymentPackage()
    {
        return $this->belongsTo(PaymentPackage::class);
    }

    // Define the relationship with the Coupon model
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
