<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class PokeApiClient
{
    private const BASE_URL = 'https://pokeapi.co/api/v2';

    public function listNames(): array
    {
        $count = Http::get(self::BASE_URL.'/pokemon', ['limit' => 1])->json('count');

        return collect(
            Http::get(self::BASE_URL.'/pokemon', ['limit' => $count])->json('results')
        )->map(fn (array $pokemon) => $pokemon['name'])->all();
    }

    public function findMany(array $names): array
    {
        $responses = Http::pool(fn (Pool $pool) => collect($names)->map(
            fn (string $name) => $pool->as($name)->get(self::BASE_URL."/pokemon/{$name}")
        )->all());

        return collect($responses)->map(fn ($response, string $name) => [
            'name' => $name,
            'id' => $response->json('id'),
            'image' => $response->json('sprites.front_default'),
        ])->all();
    }
}
