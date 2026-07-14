<?php

namespace App\Services;

class PokemonSearcher
{
    public function __construct(private PokemonCacheService $cache)
    {
    }

    public function search(?string $query, int $page, int $perPage): array
    {
        $names = $this->cache->allNames();

        $filtered = $query === null
            ? $names
            : array_values(array_filter(
                $names,
                fn (string $name) => str_contains(strtolower($name), strtolower($query))
            ));

        $total = count($filtered);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $pageNames = array_slice($filtered, ($page - 1) * $perPage, $perPage);
        $details = $this->cache->details($pageNames);

        return [
            'data' => array_values(array_map(fn (string $name) => $details[$name], $pageNames)),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }
}
