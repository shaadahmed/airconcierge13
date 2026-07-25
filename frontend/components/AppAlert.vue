<script setup>
defineProps({
  errors: {
    type: Object,
    default: () => ({}),
  },
  type: {
    type: String,
    default: 'error',
  },
})
</script>

<template>
  <VAlert
    v-if="errors?.general"
    :type="type"
    class="mb-4"
  >
    {{ Array.isArray(errors.general) ? errors.general[0] : errors.general }}
  </VAlert>
</template>
