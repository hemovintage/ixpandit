# Pokemon Finder

A web app for searching Pokémon by partial name, built for the Ixpandit technical test. The backend (Laravel 12) fetches and caches data from [PokeAPI](https://pokeapi.co/docs/v2) via raw HTTP calls — no Pokémon-API wrapper libraries — and the frontend (Vue 3) provides the search UI.

## Stack

- **Backend**: Laravel 12 (PHP 8.4), Redis (via `predis`) for caching PokeAPI responses.
- **Frontend**: Vue 3 + Vite, plain JavaScript.
- **Infrastructure**: Docker Compose (`backend`, `frontend`, `redis` services).

## Requirements

Docker and Docker Compose. Nothing else needs to be installed locally.

## Running the app

```
docker compose up --build
```

- Frontend: http://localhost:5173
- Backend API: http://localhost:8000/api/pokemons

No manual `.env` setup is needed — the backend container automatically copies `.env.example` to `.env` and generates the app key on first run.

## How it's put together

**Backend.** I split the PokeAPI logic into a few small classes instead of one big one, mostly to keep each responsibility obviously separate and easy to reason about on its own:

- `PokeApiClient` is the only class that talks to PokeAPI. It fetches the full Pokémon name list, and fetches details (id, sprite) for a batch of names concurrently via `Http::pool()`, rather than one request at a time.
- `PokemonCacheService` wraps that client with Redis caching (1 hour TTL), and only fetches details for names actually missing from the cache.
- `Paginator` handles pagination math only — slicing an array, clamping page numbers. It has no knowledge of Pokémon at all, by design.
- `PokemonSearcher` filters the cached name list by partial, case-insensitive match, then ties pagination and caching together.
- `PokemonController` stays thin: validate the request, delegate to `PokemonSearcher`, return JSON.

The interesting constraint here is that PokeAPI has no server-side partial-name search, so the backend fetches the full name list once, caches it, and filters in-memory. It only hydrates full details (sprite, id) for whatever's on the current page — not the entire match set — so a broad query doesn't quietly trigger hundreds of extra requests.

**Frontend.** Kept it equally simple — no router, no state library, since the app doesn't need either:

- `services/pokeApi.js` is the only file that calls the backend.
- `SearchBar.vue` requires at least 3 valid characters (letters, numbers, hyphens — no spaces or symbols) before it will search. Once that's met, it debounces for 300ms before firing automatically; dropping back below 3 characters clears the results instead of leaving stale ones on screen.
- `PokemonCard.vue`, `PaginationControls.vue`, and `AppFooter.vue` are presentational only.
- `App.vue` holds the actual state and wires everything together.

## Running the tests

**Backend** (16 tests):

```
docker compose exec -e CACHE_STORE=array backend php artisan test
```

The `-e CACHE_STORE=array` override matters: the container runs against real Redis, so without it the tests would read and write actual cached data instead of a clean, isolated store.

**Frontend** (8 tests):

```
docker compose exec frontend npx vitest run
```

## Linting

```
docker compose exec backend ./vendor/bin/pint
docker compose exec frontend npm run lint
```

Backend uses Pint, frontend uses oxlint + ESLint.

## API

`GET /api/pokemons?query=pika&page=1&per_page=20`

```json
{
  "data": [
    { "name": "pikachu", "id": 25, "image": "https://..." }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 17,
    "last_page": 1
  }
}
```

All parameters are optional: `query` filters by partial name, `page` defaults to 1, `per_page` defaults to 20 (max 100).
