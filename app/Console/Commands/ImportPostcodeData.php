<?php

namespace App\Console\Commands;

use App\Services\ImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\NoReturn;

class ImportPostcodeData extends Command
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-postcode-data {--batchSize=1000} {--maxRecords=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import postcode data from CSV file';

    /**
     * Execute the console command.
     */
    #[NoReturn] public function handle(): void
    {
        // Configure PHP to use a large memory limit
        ini_set('memory_limit', '2G');
        set_time_limit(0); // Allow script to run indefinitely

        // Read the CSV file
        $postcodeData = storage_path('app/private/postcodedata.csv');
        $maxRecords = $this->option('maxRecords');
        $batchSize = $this->option('batchSize');

        if (($handle = fopen($postcodeData, 'r')) !== false) {
            // Skip the header row if present
            fgetcsv($handle);

            $postcodes = [];
            $count = 0;

            // Begin a database transaction
            DB::beginTransaction();

            // Loop through the CSV data
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $postcode = trim($data[0] ?? '');
                $latitude = trim($data[42] ?? '');
                $longitude = trim($data[43] ?? '');

                // Validate data and add to postcodes array if valid
                if ($this->isValidPostcodeData($postcode, $latitude, $longitude)) {
                    $postcodes[] = [
                        'postcode' => $postcode,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $count++;

                // Insert the postcode into the database if the batch size is reached
                if ($count % $batchSize === 0) {
                    // Insert the postcode into the database
                    DB::table('postcodes')->insert($postcodes);
                    // Clear the postcodes array for the next batch
                    $postcodes = [];
                    $this->warn("Postcode data imported $count out of $maxRecords postcodes.");
                }

                // Exit the loop if the maximum number of records is reached and $maxRecords is greater than 0
                if ($count >= $maxRecords && $maxRecords > 0) {
                    break;
                }
            }

            // Insert the remaining records
            if (!empty($postcodes)) {
                DB::table('postcodes')->insert($postcodes);
            }

            // Commit the transaction
            DB::commit();

            // Close the file handle
            fclose($handle);

            $this->info('Postcode data imported successfully.');
        } else {
            $this->error('Error reading CSV file.');
        }
    }

    /**
     * Validate the postcode data.
     *
     * @param string $postcode
     * @param string $latitude
     * @param string $longitude
     * @return bool
     */
    public function isValidPostcodeData(string $postcode, string $latitude, string $longitude): bool
    {
        // Basic check to see if postcode, latitude, and longitude are not empty
        if (empty($postcode) || empty($latitude) || empty($longitude)) {
            return false;
        }

        return $this->importService->validatePostcodeData($postcode, $latitude, $longitude);
    }
}
