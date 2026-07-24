<script setup>
const dashboard = useDashboardStore()
const stats = computed(() => dashboard.data.stats || {})

onMounted(() => dashboard.load())

definePageMeta({ middleware: 'auth', layout: 'default' })
</script>

<template>
  <div>
    <VAlert
      v-if="dashboard.errors.general"
      type="error"
      class="mb-6"
    >
      {{ dashboard.errors.general[0] }}
    </VAlert>
    <VRow>
      <VCol
        v-for="(value, label) in stats"
        :key="label"
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard>
          <VCardText>
            <div class="text-body-2 text-capitalize">{{ String(label).replaceAll('_', ' ') }}</div>
            <div class="text-h4 mt-2">{{ value }}</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <VProgressLinear
      v-if="dashboard.loading"
      indeterminate
      class="mt-6"
    />
  </div>
</template>
