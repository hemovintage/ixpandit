<?php

namespace Tests\Unit\Services;

use App\Services\PokeApiClient;
use App\Services\PokemonCacheService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PokemonCacheServiceTest extends TestCase
{
    #[Test]
    public function it_caches_the_full_name_list_after_first_fetch(): void
    {
        $client = Mockery::mock(PokeApiClient::class);
        $client->shouldReceive('listNames')->once()->andReturn(['bulbasaur', 'ivysaur']);

        $service = new PokemonCacheService($client);

        $first = $service->allNames();
        $second = $service->allNames();

        $this->assertSame(['bulbasaur', 'ivysaur'], $first);
        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_only_fetches_details_for_names_missing_from_cache(): void
    {
        Cache::put('pokeapi.detail.bulbasaur', ['name' => 'bulbasaur', 'id' => 1, 'image' => 'bulbasaur.png']);

        $client = Mockery::mock(PokeApiClient::class);
        $client->shouldReceive('findMany')
            ->once()
            ->with(['ivysaur'])
            ->andReturn(['ivysaur' => ['name' => 'ivysaur', 'id' => 2, 'image' => 'ivysaur.png']]);

        $service = new PokemonCacheService($client);

        $result = $service->details(['bulbasaur', 'ivysaur']);

        $this->assertSame('bulbasaur.png', $result['bulbasaur']['image']);
        $this->assertSame('ivysaur.png', $result['ivysaur']['image']);
    }

    #[Test]
    public function it_stores_freshly_fetched_details_into_the_cache(): void
    {
        $client = Mockery::mock(PokeApiClient::class);
        $client->shouldReceive('findMany')
            ->once()
            ->with(['pikachu'])
            ->andReturn(['pikachu' => ['name' => 'pikachu', 'id' => 25, 'image' => 'pikachu.png']]);

        $service = new PokemonCacheService($client);
        $service->details(['pikachu']);

        $this->assertSame(
            ['name' => 'pikachu', 'id' => 25, 'image' => 'pikachu.png'],
            Cache::get('pokeapi.detail.pikachu')
        );
    }
}
