<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;
    
    protected $fillable = [
    	'name',
    	'address',
    ];

    public function deliveryZones(): HasMany
    {
    	return $this->hasMany(DeliveryZone::class, 'restaurant_id', 'id');
    }
}
