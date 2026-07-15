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
