<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PokemonSearcher;
use Illuminate\Http\Request;

class PokemonController extends Controller
{
    public function __construct(private PokemonSearcher $searcher) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json($this->searcher->search(
            $validated['query'] ?? null,
            (int) ($validated['page'] ?? 1),
            (int) ($validated['per_page'] ?? 20),
        ));
    }
}
