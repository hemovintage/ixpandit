<script setup>
defineProps({
  pokemon: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <div class="pokemon-card">
    <img :src="pokemon.image" :alt="pokemon.name" />
    <p>{{ pokemon.name }}</p>
  </div>
</template>

<style scoped>
.pokemon-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
}

.pokemon-card img {
  width: 64px;
  height: 64px;
}

.pokemon-card p {
  text-transform: capitalize;
}
</style>
