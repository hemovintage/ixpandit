<script setup>
defineProps({
  currentPage: {
    type: Number,
    required: true,
  },
  lastPage: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['change-page'])
</script>

<template>
  <div class="pagination" v-if="lastPage > 1">
    <button :disabled="currentPage <= 1" @click="emit('change-page', currentPage - 1)">
      Anterior
    </button>
    <span>Página {{ currentPage }} de {{ lastPage }}</span>
    <button :disabled="currentPage >= lastPage" @click="emit('change-page', currentPage + 1)">
      Siguiente
    </button>
  </div>
</template>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
}

button {
  padding: 0.4rem 1rem;
  cursor: pointer;
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}
</style>
