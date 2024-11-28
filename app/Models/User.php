<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        'password',
        'social_id',
        'provider',
        'is_active',
        'street', 
        'number', 
        'apartment', 
        'floor', 
        'zip_code',
        'address_comment'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'payment_package_id',
        'social_id',
        'provider'
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

    public function city()
    {
        return $this->belongsTo(City::class);
    }

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

    public function subCategories()
    {
        return $this->belongsToMany(InteractionSubCategory::class, 'user_sub_categories');
    }

    public static function saveInstance($values): User
    {
        DB::beginTransaction();

        $user = new self();
        $user->fill($values);
        $user->uuid = Str::uuid();
        if ($user->save()) {
            $details = new UserDetail();
            $details->user_id = $user->id;
            $details->save();

            DB::commit();

            return $user;
        }
    }

    public function getPath()
    {
        return 'users/' . $this->uuid;
    }

    public function getFile($fileType, $ext)
    {
        $path = $this->getPath() . '/' . $fileType . '.' . $ext;
        if (!Storage::exists($path)) {
            return null;
        }

        return url(Storage::url($path));
    }

    public function getPrefixFiles()
    {
        $files = collect();

        for ($i = 1; $i <= 3; $i++) {
            $file = $this->getFile('prefix_name_' . $i , 'mp3');
            if ($file) {
                $files->push($file);
            }
        }

        return $files;
    }
}
