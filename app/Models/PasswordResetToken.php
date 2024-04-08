<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class PasswordResetToken extends BaseModel
{

    public static function saveInstance($email)
    {
        DB::beginTransaction();

        self::where('email', $email)->delete();

        $token = bin2hex(random_bytes(32));
        $passwordResetToken = new self();
        $passwordResetToken->email = $email;
        $passwordResetToken->token = $token;
        $passwordResetToken->save();

        DB::commit();

        return $token;
    }
}
