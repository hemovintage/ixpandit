<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'

const emit = defineEmits(['search', 'invalid'])

const query = ref('')
const isValidQuery = computed(() => /^[a-zA-Z0-9-]{3,}$/.test(query.value))

let debounceTimer = null

function triggerSearch() {
  if (isValidQuery.value) {
    emit('search', query.value)
  }
}

watch(query, () => {
  clearTimeout(debounceTimer)

  if (isValidQuery.value) {
    debounceTimer = setTimeout(triggerSearch, 300)
  } else {
    emit('invalid')
  }
})

onBeforeUnmount(() => clearTimeout(debounceTimer))
</script>

<template>
  <form class="search-bar" @submit.prevent="triggerSearch">
    <input v-model="query" type="text" placeholder="Ingrese el nombre a buscar" />
    <button type="submit" :disabled="!isValidQuery">Buscar</button>
  </form>
</template>

<style scoped>
.search-bar {
  display: flex;
  gap: 0.5rem;
}

input {
  flex: 1;
  padding: 0.5rem;
  font-size: 1rem;
}

button {
  padding: 0.5rem 1.5rem;
  font-size: 1rem;
  cursor: pointer;
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}
</style>
