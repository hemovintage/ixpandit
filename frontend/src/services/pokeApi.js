const BASE_URL = import.meta.env.VITE_API_URL

export async function searchPokemon(query, page, perPage = 20) {
  const params = new URLSearchParams({ query, page, per_page: perPage })
  const response = await fetch(`${BASE_URL}/pokemons?${params}`)

  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`)
  }

  return response.json()
}
