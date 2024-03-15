<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'city',
        'mobile',
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'payment_package_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:d/m/Y',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function paymentPackage()
    {
        return $this->belongsTo(PaymentPackage::class);
    }

    public function details()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function interactions()
    {
        return $this->belongsToMany(Interaction::class,'user_interactions');
    }

    public function hobbies()
    {
        return $this->belongsToMany(Hobby::class, 'user_hobbies');
    }

    public function sentences()
    {
        return $this->belongsToMany(Translation::class, 'user_sentences', 'user_id', 'sentence_id');
    }

    public static function saveInstance($values): User
    {
        DB::beginTransaction();

        $user = new self();
        $user->fill($values);
        if ($user->save()) {
            $details = new UserDetail();
            $details->user_id = $user->id;
            $details->save();

            DB::commit();

            return $user;
        }
    }
}
