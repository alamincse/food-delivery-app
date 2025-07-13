<?php

namespace App\Http\Controllers\Api;

use App\Notifications\OrderAssignedNotification;
use App\Http\Requests\Api\OrderStatusRequest;
use App\Http\Requests\Api\OrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Response\ApiResponse;
use App\Services\OrderService;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use App\Models\DeliveryMan;
use App\Models\Order;

class OrderController extends Controller
{
    public function order(OrderRequest $request)
    {
        try {
            $restaurantId = data_get($request, 'restaurant_id');
            $deliveryLat = data_get($request, 'delivery_lat');
            $deliveryLng = data_get($request, 'delivery_lng');
            $totalAmount = data_get($request, 'total_amount');

            $orderService = app()->make(OrderService::class, [
                                    'restaurantId' => $restaurantId,
                                    'deliveryLat' => $deliveryLat,
                                    'deliveryLng' => $deliveryLng,
                                ]);

            $deliveryZone = $orderService->getValidateDeliveryZone();

            if (! $deliveryZone) {
                return app(ApiResponse::class)->error('Delivery address is outside the delivery zone', 'Error');
            }

            $nearestDeliveryMen = $orderService->getNearestDeliveryMan();

            if (! $nearestDeliveryMen) {
                return app(ApiResponse::class)->error('No delivery person available nearby', 'Error');
            }

            $order = Order::create([
                        'user_id' => auth('admin')->id(),
                        'restaurant_id' => $restaurantId,
                        'delivery_men_id' => data_get($nearestDeliveryMen, 'id'),
                        'delivery_lat' => $deliveryLat,
                        'delivery_lng' => $deliveryLng,
                        'status' => Order::PENDING,
                        'total_amount' => $totalAmount ?? 0,
                    ]);

            $nearestDeliveryMen->notify(new OrderAssignedNotification($order));

            $data = [
                'message' => 'Order placed and waiting for the delivery man confirmation.',
                'delivery_man' => $nearestDeliveryMen->only(['id', 'name', 'latitude', 'longitude']),
                'order' => $order,
            ];

            return app(ApiResponse::class)->success($data, 'Success');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return app(ApiResponse::class)->error('Something went wrong.', 'Error');
        }
    }

    // Only Delivery Man can confirm/update their order status!
    public function statusUpdate(OrderStatusRequest $request)
    {
        try {
            $status = data_get($request, 'status');
            $orderId = data_get($request, 'order_id');

            $deliveryManId = auth('delivery_man')->id();

            $order = Order::whereKey($orderId)
                            ->where('delivery_men_id', $deliveryManId)
                            ->first();

            $order->update([
                    'delivery_men_id' => $deliveryManId,
                    'status' => $status,
                ]);

            return app(ApiResponse::class)->success('Order '.$status, 'Success');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return app(ApiResponse::class)->error('Something went wrong.', 'Error');
        }
    }
}
