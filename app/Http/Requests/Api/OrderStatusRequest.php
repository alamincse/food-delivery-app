<?php

namespace App\Http\Requests\Api;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Response\ApiResponse;
use Illuminate\Validation\Rule;
use App\Models\Order;

class OrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('delivery_man')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => [
                'required', 
                Rule::exists('orders', 'id')
            ],
            'status' => [
                'required',
                Rule::in(array_keys(Order::$status)),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $orderId = data_get($this, 'order_id');
        $deliveryManId = auth('delivery_man')->id();

        $order = Order::whereKey($orderId)
                    ->where('delivery_men_id', $deliveryManId)
                    ->exists();

        if ($orderId !== null && !$order) {
            $errorMessage = [
                'user_id' => [
                    'You have not been assigned to deliver this order.',
                ],
            ];

            $apiResponse = app(ApiResponse::class)->validationError('Validation errors!', $errorMessage);

            throw new HttpResponseException($apiResponse);
        }
    }

    public function failedValidation(Validator $validator): void
    {
        $apiResponse = app(ApiResponse::class)->validationError('Validation errors!', $validator->errors()->toArray());

        throw new HttpResponseException($apiResponse);
    }
}
