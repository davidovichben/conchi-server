<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadedFile
{
    private $ext;
    private $contents;

    public function __construct($string)
    {
        $extStart = strpos($string, '/');
        $base64Start = strpos($string, 'base64,');

        $this->ext = substr($string, $extStart + 1, $base64Start - $extStart - 2);
        $this->contents = base64_decode(substr($string, $base64Start + 7));
    }

    public function store($path, $ext = null)
    {
        $fileName = $path . '.' . ($ext ?? $this->ext);
        Storage::put($fileName, $this->contents);

    }
}
