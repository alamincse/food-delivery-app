<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\DeliveryZone;

class DeliveryZoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'restaurant_id' => [
                'required', 
                Rule::exists('restaurants', 'id')
            ],
            'type' => [
                'required', 
                Rule::in(array_keys(DeliveryZone::$zoneTypes))
            ],
            'coordinates' => 'nullable|json',
            'center_lat' => 'nullable|numeric',
            'center_lng' => 'nullable|numeric',
            'radius_km' => 'nullable|numeric',
        ];
    }
}
