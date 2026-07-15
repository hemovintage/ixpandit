<?php

namespace Tests\Unit\Services;

use App\Services\Paginator;
use App\Services\PokemonCacheService;
use App\Services\PokemonSearcher;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PokemonSearcherTest extends TestCase
{
    #[Test]
    public function it_filters_pokemon_by_partial_case_insensitive_name_match(): void
    {
        $cache = Mockery::mock(PokemonCacheService::class);
        $cache->shouldReceive('allNames')->andReturn(['Pikachu', 'Bulbasaur', 'Raichu']);
        $cache->shouldReceive('details')->andReturn([
            'Pikachu' => ['name' => 'Pikachu', 'id' => 25, 'image' => 'pikachu.png'],
            'Raichu' => ['name' => 'Raichu', 'id' => 26, 'image' => 'raichu.png'],
        ]);

        $result = (new PokemonSearcher($cache, new Paginator))->search('chu', 1, 20);

        $this->assertSame(['Pikachu', 'Raichu'], array_column($result['data'], 'name'));
    }

    #[Test]
    public function it_returns_all_pokemon_when_no_query_is_given(): void
    {
        $cache = Mockery::mock(PokemonCacheService::class);
        $cache->shouldReceive('allNames')->andReturn(['bulbasaur', 'ivysaur']);
        $cache->shouldReceive('details')->andReturn([
            'bulbasaur' => ['name' => 'bulbasaur', 'id' => 1, 'image' => 'bulbasaur.png'],
            'ivysaur' => ['name' => 'ivysaur', 'id' => 2, 'image' => 'ivysaur.png'],
        ]);

        $result = (new PokemonSearcher($cache, new Paginator))->search(null, 1, 20);

        $this->assertCount(2, $result['data']);
    }

    #[Test]
    public function it_only_requests_details_for_the_current_pages_names(): void
    {
        $cache = Mockery::mock(PokemonCacheService::class);
        $cache->shouldReceive('allNames')->andReturn(['a', 'b', 'c', 'd', 'e']);
        $cache->shouldReceive('details')
            ->once()
            ->with(['a', 'b'])
            ->andReturn([
                'a' => ['name' => 'a', 'id' => 1, 'image' => null],
                'b' => ['name' => 'b', 'id' => 2, 'image' => null],
            ]);

        (new PokemonSearcher($cache, new Paginator))->search(null, 1, 2);
    }

    #[Test]
    public function it_returns_an_empty_result_when_no_pokemon_matches_the_query(): void
    {
        $cache = Mockery::mock(PokemonCacheService::class);
        $cache->shouldReceive('allNames')->andReturn(['bulbasaur', 'ivysaur']);
        $cache->shouldReceive('details')->with([])->andReturn([]);

        $result = (new PokemonSearcher($cache, new Paginator))->search('zzzzzz', 1, 20);

        $this->assertSame([], $result['data']);
        $this->assertSame(0, $result['meta']['total']);
    }
}
