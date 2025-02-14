<?php

namespace App\Console\Commands;

use App\Services\ImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

        try {
            $file = $this->downloadAndExtractFile();
            if (!$file) {
                $this->error('Failed to download postcode data from CSV file.');
                return;
            }

            $postcodeData = storage_path('app/private/' . $file);
            $maxRecords = $this->option('maxRecords');
            $batchSize = $this->option('batchSize');

            $handle = fopen($postcodeData, 'r');
            if (!$handle) {
                $this->error('Error reading CSV file.');
                return;
            }

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
                    DB::table('postcodes')->insert($postcodes);
                    // Clear the postcodes array for the next batch
                    $postcodes = [];
                    $this->warn("Imported $count postcodes.");
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
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Download and extract the file.
     *
     * @return string|null
     */
    public function downloadAndExtractFile(): ?string
    {
        $url = env('POSTCODE_DATA_URL', 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip');
        $tempPath = storage_path('app/temp.zip');
        $extractPath = storage_path('app/private/');
        $extractedFile = 'Data/multi_csv/ONSPD_NOV_2022_UK_AB.csv';
        $destinationPath = 'ONSPD_NOV_2022_UK_AB.csv';


        try {
            $this->warn("Downloading and extracting file...");

            // Download file
            $response = Http::get($url);
            file_put_contents($tempPath, $response->body());

            // Extract zip
            $zip = new \ZipArchive();
            if ($zip->open($tempPath) === true) {
                if (!is_dir($extractPath)) {
                    mkdir($extractPath, 0755, true);
                }
                $zip->extractTo($extractPath);
                $zip->close();

                // Move the extracted file to the desired location
                if (file_exists($extractPath . $extractedFile)) {
                    Storage::move($extractedFile, $destinationPath);

                    // Clean up temporary files
                    Storage::deleteDirectory('Data');
                    Storage::deleteDirectory('Documents');
                    Storage::deleteDirectory('User Guide');
                    unlink($tempPath);

                    return $destinationPath;
                }
            } else {
                throw new \Exception('Failed to extract zip file.');
            }
        } catch (\Exception $e) {
            $this->error('Download and extraction failed: ' . $e->getMessage());
        }

        return null;
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
        return !empty($postcode) && !empty($latitude) && !empty($longitude) &&
            $this->importService->validatePostcodeData($postcode, $latitude, $longitude);
    }
}
