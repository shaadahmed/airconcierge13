<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  },
  loading: Boolean,
  errors: {
    type: Object,
    default: () => ({}),
  },
  empty: Boolean,
  emptyTitle: {
    type: String,
    default: 'No records found',
  },
  emptyDescription: {
    type: String,
    default: '',
  },
  emptyColspan: {
    type: [Number, String],
    default: 1,
  },
})
</script>

<template>
  <VCard>
    <VCardItem v-if="title || $slots.actions">
      <VCardTitle v-if="title">
        {{ title }}
      </VCardTitle>
      <template
        v-if="$slots.actions"
        #append
      >
        <slot name="actions" />
      </template>
    </VCardItem>

    <VCardText v-if="$slots.toolbar">
      <slot name="toolbar" />
    </VCardText>

    <div
      v-if="errors?.general"
      class="px-4"
    >
      <AppAlert :errors="errors" />
    </div>

    <VTable>
      <slot name="head" />
      <tbody>
        <slot />
        <EmptyState
          v-if="empty && !loading"
          as-table-row
          :colspan="emptyColspan"
          :title="emptyTitle"
          :description="emptyDescription"
        />
      </tbody>
    </VTable>

    <VProgressLinear
      v-if="loading"
      indeterminate
    />
  </VCard>
</template>
