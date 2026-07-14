<?php

use App\Http\Controllers\Api\PokemonController;
use Illuminate\Support\Facades\Route;

Route::get('/pokemons', [PokemonController::class, 'index']);
