<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\DeliveryZoneRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Response\ApiResponse;
use Illuminate\Http\Request;
use App\Models\DeliveryZone;

class DeliveryZoneController extends Controller
{
    public function store(DeliveryZoneRequest $request)
    {
    	try {
    		$type = data_get($request, 'type');
    		$restaurantId = data_get($request, 'restaurant_id');

	    	if ($type == DeliveryZone::RADIUS) {
	    		$data = [
					'restaurant_id' => $restaurantId,
			    	'type' => $type,
			    	'radius_km' => data_get($request, 'radius_km'),
			    	'center_lat' => data_get($request, 'center_lat'),
			    	'center_lng' => data_get($request, 'center_lng'),
		    	];
	    	
				$zone = DeliveryZone::create($data);
	    	} else if ($type == DeliveryZone::POLYGON) {
	    		$deliveryZone = DeliveryZone::where([
	    							'restaurant_id' => $restaurantId,
	    							'type' => DeliveryZone::POLYGON,
	    						])
	    						->first();

				$oldCoordinates = json_decode($deliveryZone?->coordinates, true);

				$newCoordinates = data_get($request, 'coordinates');

				$mergedCoordinates = array_merge($oldCoordinates ?? [], json_decode($newCoordinates, true));

				$zone = DeliveryZone::updateOrCreate(
					        [
						        'restaurant_id' => $restaurantId,
						        'type' => DeliveryZone::POLYGON,
						    ],
					        [
					        	'coordinates' => json_encode($mergedCoordinates)
					        ]
					    );
	    	}

	        return app(ApiResponse::class)->success($zone, 'Success');
    	} catch (\Exception $e) {
            Log::error($e->getMessage());

            return app(ApiResponse::class)->error('Something went wrong.', 'Error');
        }
    }
}
