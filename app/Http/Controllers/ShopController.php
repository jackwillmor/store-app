<?php

namespace App\Http\Controllers;

use App\Http\Requests\NearbyShopsRequest;
use App\Http\Requests\ShopRequest;
use App\Http\Requests\ShopsDeliveringRequest;
use App\Models\Shop;
use App\Services\ShopService;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    protected ShopService $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * Store a newly created shop.
     *
     * @param ShopRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ShopRequest $request): JsonResponse
    {
        try {
            $shop = Shop::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Shop created successfully',
                'shop' => $shop
            ], 201);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shop',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Get nearby shops within a given distance.
     *
     * @param NearbyShopsRequest $request
     * @return JsonResponse
     */
    public function getNearbyShops(NearbyShopsRequest $request): JsonResponse
    {
        try {
            $shops = $this->shopService->getNearbyShops($request->get('postcode'), $request->get('max_distance'));

            return response()->json([
                'success' => true,
                'message' => 'Nearby shops retrieved successfully',
                'shops' => $shops
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shops',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Get shops that are delivering within a given distance.
     *
     * @param ShopsDeliveringRequest $request
     * @return JsonResponse
     */
    public function getShopsDeliveringToPostcode(ShopsDeliveringRequest $request): JsonResponse
    {
        try {
            $shops = $this->shopService->getShopsDelivering($request->get('postcode'));

            return response()->json([
                'success' => true,
                'message' => 'Shops delivering to postcode retrieved successfully',
                'shops' => $shops
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shops',
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}
