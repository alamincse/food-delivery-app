<?php 

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\DeliveryManLoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Response\ApiResponse;
use Illuminate\Http\Request;
use App\Models\DeliveryMan;

class DeliveryAuthController extends Controller
{
    public function login(DeliveryManLoginRequest $request)
    {
    	try {
	        $email = data_get($request, 'email');
	        $password = data_get($request, 'password');

	        $deliveryMan = DeliveryMan::where('email', $email)->first();

	        if (!$deliveryMan || !Hash::check($password, $deliveryMan->password)) {
	            return app(ApiResponse::class)->error('Invalid credentials', 'Error');
	        }

	        $token = $deliveryMan->createToken('delivery_token')->plainTextToken;

	        $data = [
	            'access_token' => $token,
	            'token_type' => 'Bearer',
	            'user' => $deliveryMan
	        ];

	        return app(ApiResponse::class)->success($data, 'Success');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return app(ApiResponse::class)->error('Something went wrong.', 'Error');
        }
    }

    public function logout(Request $request)
    {
        $request->user('delivery_man')->currentAccessToken()->delete();

        return app(ApiResponse::class)->success('Logged out', 'Success');
    }
}
