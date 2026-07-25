<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-6">
    <div>
      <h4 class="text-h4 mb-1">
        {{ title }}
      </h4>
      <p
        v-if="subtitle"
        class="text-body-1 mb-0 text-medium-emphasis"
      >
        {{ subtitle }}
      </p>
    </div>
    <div
      v-if="$slots.actions"
      class="d-flex flex-wrap align-center gap-2"
    >
      <slot name="actions" />
    </div>
  </div>
</template>
