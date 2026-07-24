<script setup>
const reports = useReportsStore()
const filters = reactive({ year: new Date().getFullYear(), month: '', region_id: '', property_id: '' })

onMounted(() => reports.load(filters))

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <VCard
      title="Report filters"
      class="mb-6"
    >
      <VCardText>
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
    <VAlert
      v-if="reports.errors.general"
      type="error"
      class="mb-6"
    >
      {{ reports.errors.general[0] }}
    </VAlert>
    <VRow>
      <VCol
        v-for="(section, name) in reports.data"
        :key="name"
        cols="12"
        md="4"
      >
        <VCard :title="String(name).toUpperCase()">
          <VCardText>
            <pre class="text-body-2 text-wrap">{{ section }}</pre>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
