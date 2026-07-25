<script setup>
const reports = useReportsStore()
const filters = reactive({ year: new Date().getFullYear(), month: '', region_id: '', property_id: '' })
const sections = computed(() => reports.data || {})

onMounted(() => reports.load(filters))

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Reports"
      subtitle="TOT, occupancy, and performance summaries"
    />

    <VCard
      title="Report filters"
      class="mb-6"
    >
      <VCardText>
        <AppAlert :errors="reports.errors" />
        <VForm @submit.prevent="reports.load(filters)">
          <VRow>
            <VCol
              v-for="field in ['year', 'month', 'region_id', 'property_id']"
              :key="field"
              cols="12"
              md="3"
            >
              <BaseInput
                v-model="filters[field]"
                :label="field.replaceAll('_', ' ')"
                type="number"
                :error="reports.errors[field]"
              />
            </VCol>
          </VRow>
          <BaseButton
            type="submit"
            label="Run reports"
            :loading="reports.loading"
          />
        </VForm>
      </VCardText>
    </VCard>

    <VRow>
      <VCol
        v-for="(section, name) in sections"
        :key="name"
        cols="12"
        md="4"
      >
        <VCard :title="String(name).replaceAll('_', ' ')">
          <VCardText>
            <pre class="text-body-2 text-wrap">{{ section }}</pre>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        v-if="!reports.loading && Object.keys(sections).length === 0"
        cols="12"
      >
        <VCard>
          <EmptyState
            title="No report data"
            description="Adjust filters and run reports."
          />
        </VCard>
      </VCol>
    </VRow>

    <VProgressLinear
      v-if="reports.loading"
      indeterminate
      class="mt-4"
    />
  </div>
</template>
