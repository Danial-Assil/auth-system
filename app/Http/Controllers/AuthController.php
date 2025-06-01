<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordAuthRequest;
use App\Http\Requests\LogInAuthRequest;
use App\Http\Requests\RegisterAuthRequest;
use App\Http\Requests\ResendCodeAuthRequest;
use App\Http\Requests\ResetPasswordAuthRequest;
use App\Http\Requests\Verify2FAAuthRequest;
use App\Http\Requests\VerifyCodeAuthRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Services\AuthService;




class AuthController extends Controller
{


    use ApiResponseTrait;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }







    public function register(RegisterAuthRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['code'] = $request->code;

        $code = $this->authService->register($validatedData);

        return $this->successResponse(['code' => $code], 'User Registered, Verification Code Sent.');
    }









    public function verifyCode(VerifyCodeAuthRequest $request)
    {
        $validatedData = $request->validated();

        $isValid = $this->authService->verifyCode($validatedData);

        if (!$isValid) {
            return $this->errorResponse('Invalid Or Expired Code', 400);
        }

        return $this->successResponse(null, 'Email Verified Successfully');
    }










    public function resendCode(ResendCodeAuthRequest $request)
    {
        $validatedData = $request->validated();

        $code = $this->authService->resendCode($validatedData);

        return $this->successResponse(['code' => $code], 'Verification Code Resent');
    }







    public function login(LogInAuthRequest $request)
    {
        $validatedData = $request->validated();

        $result = $this->authService->login($validatedData);

        if (!$result['status']) {
            if (isset($result['requires_2fa']) && $result['requires_2fa']) {
                return $this->successResponse(null, $result['message']); // <--- هذا هو التعديل
            }

            if ($result['message'] === 'Invalid Credentials') {
                return $this->unauthorizedResponse($result['message']);
            }

            return $this->forbiddenResponse($result['message']);

    }

    }




    public function refreshToken(Request $request)
    {
        $user = $request->user();

        $newToken = $this->authService->refreshToken($user);

        return $this->successResponse(['Access_Token' => $newToken], 'Token Refreshed');
    }





    public function forgotPassword(ForgetPasswordAuthRequest $request)
    {
        $validatedData = $request->validated();

        $this->authService->sendPasswordResetLink($validatedData);

        return $this->successResponse(null, 'Password reset link sent successfully.');
    }





    public function resetPassword(ResetPasswordAuthRequest $request)
    {
        $data = $request->validated();
        $cachedToken = Cache::get('password_reset_' . $data['email']);

        if (!$cachedToken || $cachedToken !== $data['token']) {
            return $this->errorResponse('Invalid or expired token.', 400);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user)
            return $this->errorResponse('User not found', 404);

        $user->password = bcrypt($data['password']);
        $user->save();

        Cache::forget('password_reset_' . $data['email']);

        return $this->successResponse(null, 'Password has been reset successfully.');
    }




    public function verify2FA(Verify2FAAuthRequest $request)
{
    $validatedData = $request->validated();

    $result = $this->authService->verify2FA($validatedData);

    if (!$result['status']) {
        return $this->errorResponse($result['message'], 400);
    }

    return $this->successResponse(['Access_Token' => $result['token']], '2FA Verified Successfully');
}





}
