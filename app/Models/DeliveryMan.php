<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class DeliveryMan extends Authenticatable
{
	use HasApiTokens, Notifiable;

    protected $fillable = [
    	'name',
    	'email', 
    	'password',
    	'latitude',
    	'longitude',
    	'is_available',
    ];

    protected $hidden = [
    	'password',
    ];
}
