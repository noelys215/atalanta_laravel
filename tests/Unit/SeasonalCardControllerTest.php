<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SeasonalCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeasonalCardControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_all_seasonal_cards()
    {
        // Create some seasonal cards in the database
        SeasonalCard::factory()->count(3)->create();

        // Make a GET request to the index route
        $response = $this->getJson('/api/seasonal-cards');

        // Assert the response status is 200
        $response->assertStatus(200);

        // Assert the response contains 3 seasonal cards
        $response->assertJsonCount(3);
    }


    /** @test */
    public function it_returns_404_if_seasonal_card_not_found()
    {
        // Make a GET request to the show route with a non-existing slug
        $response = $this->getJson('/api/seasonal-cards/non-existing-slug');

        // Assert the response status is 404
        $response->assertStatus(404);
    }
}
