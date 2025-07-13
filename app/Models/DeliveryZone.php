<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasFactory;
    
    protected $fillable = [
    	'restaurant_id',
    	'type',
    	'coordinates',
    	'radius_km',
    	'center_lat',
    	'center_lng',
    ];

    protected $casts = [
    	'coordinates' => 'array',
    ];

    public const POLYGON = 'polygon';
    
    public const RADIUS = 'radius';

    public static array $zoneTypes = [
        self::POLYGON => 'Polygon Zone', // Polygon coordinates zone
        self::RADIUS => 'Radius Zone', // Radius based zone
    ];

    public function restaurant(): HasOne
    {
    	return $this->hasOne(Restaurant::class, 'restaurant_id', 'id');
    }
}
