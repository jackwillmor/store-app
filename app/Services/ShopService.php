<?php

namespace App\Services;

use App\Repositories\ShopRepository;

class ShopService
{
    protected PostcodeService $postcodeService;
    protected ShopRepository $shopRepository;

    public function __construct(PostcodeService $postcodeService, ShopRepository $shopRepository)
    {
        $this->postcodeService = $postcodeService;
        $this->shopRepository = $shopRepository;
    }

    /**
     * Get nearby shops within a given distance.
     * Uses Haversine Formula to calculate distance between two points on Earth's surface.
     *
     * @param string $postcode
     * @param float $maxDistance
     * @return array
     */
    public function getNearbyShops(string $postcode, float $maxDistance = 1): array
    {
        $postcodeData = $this->postcodeService->getPostcodeData($postcode);
        if (!$postcodeData) {
            return [];
        }

        // Retrieve latitude and longitude from postcode data
        $latitude = $postcodeData->latitude;
        $longitude = $postcodeData->longitude;

        return $this->shopRepository->getShopsNearby($latitude, $longitude, $maxDistance)->toArray();
    }

    /**
     * Get shops that are delivering within a given distance.
     *
     * @param string $postcode
     * @return array
     */
    public function getShopsDelivering(string $postcode): array
    {
        $postcodeData = $this->postcodeService->getPostcodeData($postcode);
        if (!$postcodeData) {
            return [];
        }

        // Retrieve latitude and longitude from postcode data
        $latitude = $postcodeData->latitude;
        $longitude = $postcodeData->longitude;

        return $this->shopRepository->getShopsDeliveringToLocation($latitude, $longitude)->toArray();
    }
}
