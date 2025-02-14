<?php

namespace App\Services;

use App\Models\Postcode;
use Illuminate\Support\Facades\DB;

class ImportService
{
    /**
     * Validate Postcode Data and check if postcode already exists.
     *
     * @param string $postcode
     * @param string $latitude
     * @param string $longitude
     * @return bool
     */
    public function validatePostcodeData(string $postcode, string $latitude, string $longitude): bool
    {
        if ($this->validatePostcode($postcode) && $this->validateLatitude($latitude) && $this->validateLongitude($longitude)) {
            return !$this->postcodeAlreadyExists($postcode);
        }

        return false;
    }

    /**
     * Check if postcode already exists.
     *
     * @param string $postcode
     * @return bool
     */
    public function postcodeAlreadyExists(string $postcode): bool
    {
        return DB::table('postcodes')->where('postcode', $postcode)->exists();
    }

    /**
     * Validate a UK postcode.
     *
     * @param string $postcode
     * @return bool
     */
    public function validatePostcode(string $postcode): bool
    {
        // Regular expression pattern for UK postcodes
        $pattern = '/^([A-Z]{1,2}[0-9]{1,2}[A-Z]?)\s?[0-9][A-Z]{2}$/i';

        // Ensure the postcode is not empty and matches the pattern
        return !empty($postcode) && preg_match($pattern, strtoupper(trim($postcode))) === 1;
    }

    /**
     * Validate a UK latitude.
     *
     * @param string $latitude
     * @return bool
     */
    public function validateLatitude(string $latitude): bool
    {
        // Ensure the latitude is a valid number, finite, and within the valid range
        return is_numeric($latitude) && is_finite($latitude) && $latitude >= -90 && $latitude <= 90;
    }

    /**
     * Validate a UK longitude.
     *
     * @param string $longitude
     * @return bool
     */
    public function validateLongitude(string $longitude): bool
    {
        // Ensure the longitude is a valid number, finite, and within the valid range
        return is_numeric($longitude) && is_finite($longitude) && $longitude >= -180 && $longitude <= 180;
    }
}
