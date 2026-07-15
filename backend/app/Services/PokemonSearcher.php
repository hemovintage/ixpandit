<?php

namespace App\Services;

class PokemonSearcher
{
    public function __construct(
        private PokemonCacheService $cache,
        private Paginator $paginator,
    ) {
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

        $paginated = $this->paginator->paginate($filtered, $page, $perPage);
        $details = $this->cache->details($paginated['items']);

        return [
            'data' => array_values(array_map(fn (string $name) => $details[$name], $paginated['items'])),
            'meta' => $paginated['meta'],
        ];
    }
}
