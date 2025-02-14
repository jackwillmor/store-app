<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShopRepository
{
    /**
     * Get shops within a specified distance.
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $maxDistance
     * @return \Illuminate\Support\Collection
     */
    public function getShopsNearby(float $latitude, float $longitude, float $maxDistance): Collection
    {
        return DB::table('shops')
            ->selectRaw('
                id,
                name,
                type,
                status,
                max_delivery_distance,
                latitude,
                longitude,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?)) * sin(radians(latitude)))) AS distance
            ', [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $maxDistance)
            ->where('status', 'open')
            ->orderBy('distance')
            ->get();
    }

    /**
     * Get shops that are capable of delivering within a specified distance.
     *
     * @param float $latitude
     * @param float $longitude
     * @return \Illuminate\Support\Collection
     */
    public function getShopsDeliveringToLocation(float $latitude, float $longitude): Collection
    {
        return DB::table('shops')
            ->selectRaw('
                id,
                name,
                type,
                status,
                max_delivery_distance,
                latitude,
                longitude,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?)) * sin(radians(latitude)))) AS distance
            ', [$latitude, $longitude, $latitude])
            ->havingRaw('distance <= max_delivery_distance')
            ->where('status', 'open')
            ->orderBy('distance')
            ->get();
    }
}
