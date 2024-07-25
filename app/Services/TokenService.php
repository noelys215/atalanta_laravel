<?php

namespace App\Services;

use Illuminate\Support\Facades\JWT;

class TokenService
{
    public function createToken($data)
    {
        return JWT::encode($data, env('JWT_SECRET'), 'HS256');
    }
}
