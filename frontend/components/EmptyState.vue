<script setup>
defineProps({
  title: {
    type: String,
    default: 'Nothing here yet',
  },
  description: {
    type: String,
    default: '',
  },
  colspan: {
    type: [Number, String],
    default: 1,
  },
  asTableRow: Boolean,
})
</script>

<template>
  <tr v-if="asTableRow">
    <td :colspan="colspan">
      <div class="text-center py-8">
        <p class="text-body-1 font-weight-medium mb-1">
          {{ title }}
        </p>
        <p
          v-if="description"
          class="text-body-2 text-medium-emphasis mb-0"
        >
          {{ description }}
        </p>
        <slot />
      </div>
    </td>
  </tr>
  <div
    v-else
    class="text-center py-10"
  >
    <p class="text-body-1 font-weight-medium mb-1">
      {{ title }}
    </p>
    <p
      v-if="description"
      class="text-body-2 text-medium-emphasis mb-0"
    >
      {{ description }}
    </p>
    <slot />
  </div>
</template>
