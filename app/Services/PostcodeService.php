<?php

namespace App\Services;

use App\Models\Postcode;
use Illuminate\Support\Facades\DB;

class PostcodeService
{
    /**
     * @param string $postcode
     * @return object|null
     */
    public function getPostcodeData(string $postcode): ?object
    {
        return DB::table('postcodes')->where('postcode', $postcode)->first();
    }
}
