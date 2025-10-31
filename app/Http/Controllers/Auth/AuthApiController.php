<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResponseResource;
use App\Models\User;
use App\Traits\CommonResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthApiController extends Controller
{
    use CommonResponse, ValidatesRequests;
    final public function registration(UserRegisterRequest $request): JsonResponse
    {
        try {
            $user = User::query()->create([
                'name'     => $request->input('name'),
                'email'    => $request->input('email'),
                'phone'    => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
            ]);
            $token = $user->createToken('automatedpros')->plainTextToken;

            $this->data           = [
                'token' => $token,
                'user'  => new UserResponseResource($user),
            ];
            $this->status_message = __('Registration Successful.');
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            Log::info('API_USER_REGISTRATION_FAILED', ['data' => $request->all(), 'error' => $throwable]);
            $this->status_message = 'Failed! ' . $throwable->getMessage();
            $this->status_code    = $this->status_code_failed;
            $this->status         = false;
        }
        return $this->commonApiResponse();
    }
    final public function login(Request $request)
    {
        try {
            $user = User::query()->where('email', $request->input('email'))->first();

            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                $this->status_message = 'The provided credentials are incorrect.';
                $this->status_code    = $this->status_code_failed;
                $this->status         = false;
                return $this->commonApiResponse();
            }

            $token = $user->createToken('automatedpros')->plainTextToken;

            $this->data           = [
                'token' => $token,
                'user'  => new UserResponseResource($user),
            ];
            $this->status_message = __('Login Successful.');
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            Log::info('API_USER_LOGIN_FAILED', ['data' => $request->all(), 'error' => $throwable]);
            $this->status_message = 'Failed! ' . $throwable->getMessage();
            $this->status_code    = $this->status_code_failed;
            $this->status         = false;
        }
        return $this->commonApiResponse();
    }

    final public function logout(): JsonResponse
    {
        try {
            DB::beginTransaction();
            auth()->user()?->tokens()->delete();
            $this->status_message = __('Logout successful');
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->status_message = 'Failed! ' . $throwable->getMessage();
            $this->status_code    = $this->status_code_failed;
            $this->status         = false;
        }
        return $this->commonApiResponse();
    }
}
