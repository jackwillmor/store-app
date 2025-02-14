<?php

namespace Tests\Unit\Services;

use App\Repositories\ShopRepository;
use App\Services\ShopService;
use App\Services\PostcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ShopService $shopService;
    protected PostcodeService $postcodeServiceMock;
    protected ShopRepository $shopRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        // Mock dependencies
        $this->postcodeServiceMock = Mockery::mock(PostcodeService::class);
        $this->shopRepositoryMock = Mockery::mock(ShopRepository::class);

        // Instantiate the ShopService with the mocked dependencies
        $this->shopService = new ShopService($this->postcodeServiceMock, $this->shopRepositoryMock);
    }

    #[Test]
    public function it_returns_nearby_shops_within_given_distance()
    {
        // Arrange
        $postcode = 'EC1A 1BB';
        $maxDistance = 5;

        // Create a mock Postcode data
        $postcodeData = collect([
            (object)[
                'id' => 1,
                'postcode' => 'EC1A 1BB',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
            ]
        ])->first();

        $this->postcodeServiceMock->shouldReceive('getPostcodeData')
            ->once()
            ->with($postcode)
            ->andReturn($postcodeData);

        // Create a mock Shop data
        $shopData = collect([
            (object)[
                'id' => 1,
                'name' => 'Test Shop',
                'type' => 'restaurant',
                'status' => 'open',
                'max_delivery_distance' => 1,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
            ],
            (object)[
                'id' => 2,
                'name' => 'Test Shop 2',
                'type' => 'restaurant',
                'status' => 'open',
                'max_delivery_distance' => 5,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
            ]
        ]);

        $this->shopRepositoryMock
            ->shouldReceive('getShopsNearby')
            ->once()
            ->with($postcodeData->latitude, $postcodeData->longitude, $maxDistance)
            ->andReturn($shopData);

        // Act
        $shops = $this->shopService->getNearbyShops($postcode, $maxDistance);

        // Assert
        $this->assertCount(2, $shops);
        $this->assertEquals('Test Shop', $shops[0]->name);
        $this->assertEquals('Test Shop 2', $shops[1]->name);
    }

    #[Test]
    public function it_returns_empty_if_postcode_not_found()
    {
        // Arrange
        $postcode = 'EC1A 1BB';
        $maxDistance = 5;

        // Mock PostcodeService to return empty array (no data found for the postcode)
        $this->postcodeServiceMock->shouldReceive('getPostcodeData')
            ->once()
            ->with($postcode)
            ->andReturn(null);

        // Act
        $shops = $this->shopService->getNearbyShops($postcode, $maxDistance);

        // Assert
        $this->assertEmpty($shops);
    }

    #[Test]
    public function it_returns_shops_that_can_deliver_to_a_given_postcode()
    {
        // Arrange
        $postcode = 'EC1A 1BB';

        $postcodeData = collect([
            (object)[
                'id' => 1,
                'postcode' => 'EC1A 1BB',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
            ]
        ])->first();

        $this->postcodeServiceMock->shouldReceive('getPostcodeData')
            ->once()
            ->with($postcode)
            ->andReturn($postcodeData);

        // Create a mock Shop data
        $shopData = collect([
            (object)[
                'id' => 1,
                'name' => 'Test Shop',
                'type' => 'restaurant',
                'status' => 'open',
                'max_delivery_distance' => 10,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'distance' => 0,
            ]
        ]);

        $this->shopRepositoryMock
            ->shouldReceive('getShopsDeliveringToLocation')
            ->once()
            ->with($postcodeData->latitude, $postcodeData->longitude)
            ->andReturn($shopData);

        $shops = $this->shopService->getShopsDelivering($postcode);

        // Assert that the returned shops are within the expected results
        $this->assertCount(1, $shops);
        $this->assertEquals('Test Shop', $shops[0]->name);
    }

    #[Test]
    public function it_returns_empty_if_it_not_find_shops_that_can_deliver_to_a_given_postcode()
    {
        // Arrange
        $postcode = 'EC1A 1BB';

        $this->postcodeServiceMock->shouldReceive('getPostcodeData')
            ->once()
            ->with($postcode)
            ->andReturn(null);

        // Act
        $shops = $this->shopService->getShopsDelivering($postcode);

        // Assert
        $this->assertEmpty($shops);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
