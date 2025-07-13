<?php 

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRegisterRequest;
use App\Http\Requests\Api\UserLoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Response\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(UserLoginRequest $request)
    {
        try {
            $email = data_get($request, 'email');
            $password = data_get($request, 'password');

            $user = User::where('email', $email)->first();

            if (!$user || !Hash::check($password, $user->password)) {
                return app(ApiResponse::class)->error('Invalid credentials', 'Error');
            }

            $token = $user->createToken('admin_token')->plainTextToken;

            $data = [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ];

            return app(ApiResponse::class)->success($data, 'Success');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return app(ApiResponse::class)->error('Something went wrong.', 'Error');
        }
    }

    public function register(UserRegisterRequest $request)
    {
        try {
            $data = [
                'name' => data_get($request, 'name'),
                'email' => data_get($request, 'email'),
                'password' => Hash::make(data_get($request, 'password')),
            ];

            $user = User::create($data);

            return app(ApiResponse::class)->success($user, 'Success');

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return app(ApiResponse::class)->error('Something went wrong.', 'Error');
        }
    }

    public function logout(Request $request)
    {
        $request->user('admin')->currentAccessToken()->delete();

        return app(ApiResponse::class)->success('Logged out', 'Success');
    }
}
