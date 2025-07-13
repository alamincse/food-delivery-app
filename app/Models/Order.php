<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	protected $fillable = [
		'user_id',
        'restaurant_id',
        'delivery_men_id',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'status',
        'total_amount',
	];

	public const PENDING = 'pending';
	public const APPROVED = 'approved';
	public const REJECTED = 'rejected';
	public const DELIVERED = 'delivered';
	public const CANCELLED = 'cancelled';
    
    public static array $status = [
        self::PENDING => 'Pending', 
        self::APPROVED => 'Approved', 
        self::REJECTED => 'Rejected', 
        self::DELIVERED => 'Delivered', 
        self::CANCELLED => 'Cancelled', 
    ];

    public function user(): BelongsTo
	{
	    return $this->belongsTo(User::class);
	}

	public function restaurant(): BelongsTo
	{
	    return $this->belongsTo(Restaurant::class);
	}

	public function deliveryMan(): BelongsTo
	{
	    return $this->belongsTo(DeliveryMan::class);
	}
}
