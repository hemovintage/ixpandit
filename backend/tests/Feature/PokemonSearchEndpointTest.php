<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PokemonSearchEndpointTest extends TestCase
{
    #[Test]
    public function it_returns_paginated_matching_pokemon_for_a_partial_name_query(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon?limit=1' => Http::response(['count' => 2]),
            'pokeapi.co/api/v2/pokemon?limit=2' => Http::response([
                'count' => 2,
                'results' => [
                    ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/'],
                    ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ],
            ]),
            'pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'id' => 25,
                'sprites' => ['front_default' => 'pikachu.png'],
            ]),
        ]);

        $response = $this->getJson('/api/pokemons?query=pika&page=1&per_page=20');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'pikachu');
        $response->assertJsonPath('data.0.id', 25);
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.last_page', 1);
    }

    #[Test]
    public function it_applies_default_pagination_when_page_and_per_page_are_omitted(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon?limit=1' => Http::response(['count' => 2]),
            'pokeapi.co/api/v2/pokemon?limit=2' => Http::response([
                'count' => 2,
                'results' => [
                    ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/'],
                    ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ],
            ]),
            'pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'id' => 25,
                'sprites' => ['front_default' => 'pikachu.png'],
            ]),
        ]);

        $response = $this->getJson('/api/pokemons?query=pika');

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 20);
    }

    #[Test]
    public function it_rejects_an_invalid_per_page_value(): void
    {
        $response = $this->getJson('/api/pokemons?per_page=500');

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_a_json_validation_error_even_without_a_json_accept_header(): void
    {
        $response = $this->get('/api/pokemons?per_page=500');

        $response->assertStatus(422);
        $response->assertHeader('content-type', 'application/json');
    }
}
