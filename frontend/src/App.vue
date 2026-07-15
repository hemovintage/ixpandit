<script setup>
import { ref } from 'vue'
import SearchBar from './components/SearchBar.vue'
import PokemonCard from './components/PokemonCard.vue'
import PaginationControls from './components/PaginationControls.vue'
import AppFooter from './components/AppFooter.vue'
import { searchPokemon } from './services/pokeApi'

const query = ref('')
const page = ref(1)
const results = ref([])
const meta = ref(null)
const loading = ref(false)
const error = ref(null)
const hasSearched = ref(false)

async function runSearch() {
  loading.value = true
  error.value = null
  hasSearched.value = true

  try {
    const response = await searchPokemon(query.value, page.value)
    results.value = response.data
    meta.value = response.meta
  } catch {
    error.value = 'No se pudo completar la búsqueda. Intente nuevamente.'
  } finally {
    loading.value = false
  }
}

function handleSearch(newQuery) {
  query.value = newQuery
  page.value = 1
  runSearch()
}

function handleChangePage(newPage) {
  page.value = newPage
  runSearch()
}

function handleInvalid() {
  results.value = []
  meta.value = null
  hasSearched.value = false
  error.value = null
}
</script>

<template>
  <main>
    <h1>Pokemon finder</h1>
    <p>El que quiere Pokemons, que los busque.</p>

    <SearchBar @search="handleSearch" @invalid="handleInvalid" />

    <p v-if="loading">Buscando...</p>
    <p v-else-if="error">{{ error }}</p>
    <p v-else-if="hasSearched && results.length === 0">No se encontraron resultados.</p>

    <section v-if="results.length">
      <h2>Resultados de la búsqueda</h2>
      <div class="results-grid">
        <PokemonCard v-for="pokemon in results" :key="pokemon.name" :pokemon="pokemon" />
      </div>
      <PaginationControls
        v-if="meta"
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        @change-page="handleChangePage"
      />
    </section>
  </main>

  <AppFooter />
</template>

<style scoped>
main {
  max-width: 640px;
  margin: 0 auto;
  padding: 2rem;
}

.results-grid {
  display: grid;
  gap: 0.75rem;
  margin-top: 1rem;
}
</style>
