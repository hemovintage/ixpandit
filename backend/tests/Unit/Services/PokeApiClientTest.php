<?php

namespace Tests\Unit\Services;

use App\Services\PokeApiClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PokeApiClientTest extends TestCase
{
    #[Test]
    public function it_fetches_the_full_pokemon_name_list_without_limiting_results(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon?limit=1' => Http::response(['count' => 3]),
            'pokeapi.co/api/v2/pokemon?limit=3' => Http::response([
                'count' => 3,
                'results' => [
                    ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                    ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
                    ['name' => 'venusaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/3/'],
                ],
            ]),
        ]);

        $names = (new PokeApiClient)->listNames();

        $this->assertSame(['bulbasaur', 'ivysaur', 'venusaur'], $names);
        Http::assertSentCount(2);
    }

    #[Test]
    public function it_fetches_details_for_multiple_pokemon_concurrently(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'id' => 25,
                'sprites' => ['front_default' => 'pikachu.png'],
            ]),
            'pokeapi.co/api/v2/pokemon/bulbasaur' => Http::response([
                'id' => 1,
                'sprites' => ['front_default' => 'bulbasaur.png'],
            ]),
        ]);

        $details = (new PokeApiClient)->findMany(['pikachu', 'bulbasaur']);

        $this->assertSame(['name' => 'pikachu', 'id' => 25, 'image' => 'pikachu.png'], $details['pikachu']);
        $this->assertSame(['name' => 'bulbasaur', 'id' => 1, 'image' => 'bulbasaur.png'], $details['bulbasaur']);
    }

    #[Test]
    public function it_returns_an_empty_array_when_no_names_are_requested(): void
    {
        Http::fake();

        $details = (new PokeApiClient)->findMany([]);

        $this->assertSame([], $details);
        Http::assertNothingSent();
    }
}
