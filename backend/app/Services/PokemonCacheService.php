<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PokemonCacheService
{
    private const TTL = 3600;

    public function __construct(private PokeApiClient $client)
    {
    }

    public function allNames(): array
    {
        return Cache::remember('pokeapi.names', self::TTL, fn () => $this->client->listNames());
    }

    public function details(array $names): array
    {
        $result = [];
        $missing = [];

        foreach ($names as $name) {
            $cached = Cache::get($this->detailKey($name));

            if ($cached !== null) {
                $result[$name] = $cached;
            } else {
                $missing[] = $name;
            }
        }

        foreach ($this->client->findMany($missing) as $name => $detail) {
            Cache::put($this->detailKey($name), $detail, self::TTL);
            $result[$name] = $detail;
        }

        return $result;
    }

    private function detailKey(string $name): string
    {
        return "pokeapi.detail.{$name}";
    }
}
