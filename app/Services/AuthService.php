<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;




class AuthService
{
    public function register(array $validatedData)
    {
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'code' => $validatedData['code']
        ]);

        $code = rand(100000, 999999);

        Cache::put('verification_code_' . $validatedData['email'], $code, now()->addMinutes(10));
        return $code;
    }




    public function verifyCode(array $validatedData)
    {
        $email = $validatedData['email'];
        $inputCode = $validatedData['code'];

        $cachedCode = Cache::get('verification_code_' . $email);

        if ($cachedCode && $cachedCode == $inputCode) {
            $user = User::where('email', $email)->first();
            $user->email_verified_at = now();
            $user->save();


            Cache::forget('verification_code_' . $email);

            return true;
        }
        return false;
    }



    public function resendCode(array $validatedData)
    {
        $email = $validatedData['email'];


        Cache::forget('verification_code_' . $email);

        $code = rand(100000, 999999);


        Cache::put('verification_code_' . $email, $code, now()->addMinutes(10));

        return $code;
    }



    public function login(array $validatedData)
    {
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return ['status' => false, 'message' => 'Invalid Credentials'];
        }

        if (!$user->email_verified_at) {
            return ['status' => false, 'message' => 'Email not Verified'];
        }


        $code = rand(100000, 999999);


        Cache::put('2fa_' . $user->email, $code, now()->addMinutes(10));


        Mail::raw("Your verification code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your 2FA Verification Code');
        });


        return [
            'status' => false,
            'requires_2fa' => true,
            'message' => '2FA code sent to your email'
        ];
    }



    public function refreshToken($user)
    {
        $user->tokens()->delete();

        $newToken = $user->createToken('auth_token')->plainTextToken;

        return $newToken;
    }






    public function sendPasswordResetLink(array $validatedData)
    {
        $email = $validatedData['email'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        Cache::put('password_reset_' . $email, $token, now()->addMinutes(60));

        $resetLink = url("/api/reset-password?token={$token}&email=" . urlencode($email));

        Mail::to($email)->send(new PasswordResetMail($resetLink));
        return true;
    }




    public function verify2FA(array $validatedData)
    {
        $email = $validatedData['email'];
        $code = $validatedData['code'];

        $cachedCode = Cache::get('2fa_' . $email);

        if (!$cachedCode || $cachedCode != $code) {
            return ['status' => false, 'message' => 'Invalid or expired 2FA code'];
        }

        $user = User::where('email', $email)->first();


        Cache::forget('2fa_' . $email);


        $token = $user->createToken('auth_token')->plainTextToken;

        return ['status' => true, 'token' => $token];
    }






}















