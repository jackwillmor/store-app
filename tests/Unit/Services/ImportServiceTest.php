<?php

namespace Tests\Unit\Services;

use App\Services\ImportService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_postcode_data_successfully()
    {
        // Arrange
        $postcode = 'EC1A 1BB';
        $latitude = '51.5074';
        $longitude = '-0.1278';

        $importService = new ImportService();

        // Mock the postcodeAlreadyExists method to return false
        $mock = Mockery::mock(ImportService::class);
        $mock->shouldReceive('postcodeAlreadyExists')
            ->with($postcode)
            ->andReturn(false);

        // Act
        $result = $importService->validatePostcodeData($postcode, $latitude, $longitude);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_false_if_postcode_already_exists()
    {
        // Arrange
        $postcode = 'EC1A 1BB';
        $latitude = '51.5074';
        $longitude = '-0.1278';

        // Mock the postcodeAlreadyExists method to return true
        $mock = Mockery::mock(ImportService::class)->makePartial();
        $mock->shouldReceive('postcodeAlreadyExists')
            ->with($postcode)
            ->andReturn(true);

        // Act
        $result = $mock->validatePostcodeData($postcode, $latitude, $longitude);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_validates_correct_postcode_format()
    {
        // Arrange
        $importService = new ImportService();

        // Act & Assert
        $this->assertTrue($importService->validatePostcode('EC1A 1BB'));
        $this->assertFalse($importService->validatePostcode('Invalid Postcode'));
    }

    #[Test]
    public function it_validates_latitude_and_longitude()
    {
        // Arrange
        $importService = new ImportService();

        // Act & Assert
        $this->assertTrue($importService->validateLatitude('51.5074'));
        $this->assertFalse($importService->validateLatitude('200'));

        $this->assertTrue($importService->validateLongitude('-0.1278'));
        $this->assertFalse($importService->validateLongitude('200'));
    }

    #[Test]
    public function it_checks_if_postcode_exists_in_database()
    {
        // Arrange
        $postcode = 'EC1A 1BB';

        // Mock DB facade
        DB::shouldReceive('table')
            ->once()
            ->with('postcodes')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->once()
            ->with('postcode', $postcode)
            ->andReturnSelf();

        DB::shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $importService = new ImportService();

        // Act
        $result = $importService->postcodeAlreadyExists($postcode);

        // Assert
        $this->assertFalse($result);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
