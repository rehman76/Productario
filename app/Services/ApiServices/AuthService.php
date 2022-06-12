<?php


namespace App\Services\ApiServices;


use App\User;

class AuthService
{
    public function generateToken($email)
    {
        $user = User::where('email', $email)->firstOrFail();

        return $user->createToken('auth_token')->plainTextToken;
    }
}
