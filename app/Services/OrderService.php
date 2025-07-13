<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DeliveryZone;
use App\Models\DeliveryMan;

class OrderService
{
    protected int $restaurantId;
    protected float $deliveryLat;
    protected float $deliveryLng;

    public function __construct($restaurantId, $deliveryLat, $deliveryLng)
    {
        $this->restaurantId = $restaurantId;
        $this->deliveryLat = $deliveryLat;
        $this->deliveryLng = $deliveryLng;
    }

    public function getValidateDeliveryZone()
    {
        $zones = DeliveryZone::where('restaurant_id', $this->restaurantId)->get();

        $isInside = false;

        if (! blank($zones)) {
            foreach ($zones as $zone) {
                $zoneType = data_get($zone, 'type');
                $centerLat = data_get($zone, 'center_lat');
                $centerLng = data_get($zone, 'center_lng');
                $radiusKM = data_get($zone, 'radius_km');
                $points = json_decode(data_get($zone, 'coordinates'), true);

                if ($zoneType === DeliveryZone::RADIUS) {
                    $distance = $this->haversineDistance($this->deliveryLat, $this->deliveryLng, $centerLat, $centerLng);
                    
                    if ($distance <= $radiusKM) {
                        $isInside = true;
                        break;
                    }
                } else if ($zoneType === DeliveryZone::POLYGON) {
                    $pointInPolygon = $this->pointInPolygon($this->deliveryLat, $this->deliveryLng, $points);

                    if ($pointInPolygon) {
                        $isInside = true;
                        break;
                    }
                }
            }
        }

        return $isInside;
    }


    public function getNearestDeliveryMan()
    {
        $nearestDeliveryMen = null;
        $minDistance = INF; // Define the infinity value, so that the first condition check is true. 

        $deliveryMan = DeliveryMan::where('is_available', true)->get();

        if (! blank($deliveryMan)) {
            foreach ($deliveryMan as $man) {
                $manLatitude = data_get($man, 'latitude');
                $manLongitude = data_get($man, 'longitude');

                $distance = $this->haversineDistance($this->deliveryLat, $this->deliveryLng, $manLatitude, $manLongitude);

                if ($distance <= 5 && $distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestDeliveryMen = $man;
                }
            }
        }

        return $nearestDeliveryMen;
    }

    public function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);
        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = pow(sin($dlat / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dlng / 2), 2);

        return 2 * $earthRadius * asin(sqrt($a));
    }

    public function pointInPolygon($lat, $lng, array $polygon)
    {
        $inside = false;
        $j = count($polygon) - 1;


        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lng']; // X axis = lng
            $yi = $polygon[$i]['lat']; // Y axis = lat
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            $intersect = (($yi > $lat) != ($yj > $lat)) && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi + 0.00000001) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
    }
}
