<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ResetPassword extends Mailable
{
    public $clientUrl;
	public $token;

    public function __construct($token)
    {
        $this->clientUrl = config('app.client_url');
        $this->token = $token;
    }

    public function build()
    {
		return $this->markdown('emails.reset-password')->subject('איפוס סיסמה');
    }
}
