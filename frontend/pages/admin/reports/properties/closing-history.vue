<script setup>
const entities = useAdminEntitiesStore()
const endpoint = '/admin/reports/properties/closing-history'

/** @type {import('vue').Ref<'dashboard' | 'filter'>} */
const panelMode = ref('dashboard')

const filters = reactive({
  property: '',
  year: '',
})

const allRows = computed(() => entities.records[endpoint] || [])

const propertyLabel = row => row.property?.property_title || row.property_title || row.property_id || '—'

const rows = computed(() => {
  const propertyQuery = filters.property.trim().toLowerCase()
  const yearQuery = filters.year.trim()

  return allRows.value.filter(row => {
    if (propertyQuery && !String(propertyLabel(row)).toLowerCase().includes(propertyQuery))
      return false

    if (yearQuery && String(row.year) !== yearQuery)
      return false

    return true
  })
})

const stats = computed(() => {
  const list = allRows.value
  const totalRevenue = list.reduce((sum, row) => sum + Number(row.revenue || 0), 0)
  const withOccupancy = list.filter(row => row.occupancy != null)
  const years = new Set(list.map(row => row.year).filter(Boolean))

  return {
    total: list.length,
    totalRevenue,
    withOccupancy: withOccupancy.length,
    years: years.size,
  }
})

const statusChartSeries = computed(() => [stats.value.withOccupancy, Math.max(stats.value.total - stats.value.withOccupancy, 0)])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['With occupancy', 'Without'],
  colors: ['#28c76f', '#a8aaae'],
  legend: { position: 'bottom', fontSize: '13px' },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          name: { show: true, fontSize: '13px' },
          value: { show: true, fontSize: '22px', fontWeight: 600, formatter: value => String(value) },
          total: { show: true, label: 'Total', fontSize: '13px', formatter: () => String(stats.value.total) },
        },
      },
    },
  },
  stroke: { width: 0 },
  tooltip: { y: { formatter: value => `${value} rows` } },
}))

const panelTitle = computed(() => panelMode.value === 'filter' ? 'Filter closing history' : 'Closing overview')

const openPanel = mode => {
  panelMode.value = panelMode.value === mode ? 'dashboard' : mode
}

const clearFilters = () => {
  Object.assign(filters, { property: '', year: '' })
}

onMounted(() => entities.list(endpoint))

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Month Closing History"
      subtitle="Property monthly closing metrics"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="Closing history"
          :loading="entities.loading"
          :errors="entities.errors"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No closing history found"
          :empty-description="allRows.length && rows.length === 0 ? 'No rows match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Property</th>
                <th>Year</th>
                <th>Month</th>
                <th>Revenue</th>
                <th>Occupancy</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="row in rows"
            :key="row.id || `${row.property_id}-${row.year}-${row.month}`"
          >
            <td>{{ propertyLabel(row) }}</td>
            <td>{{ row.year || '—' }}</td>
            <td>{{ row.month || '—' }}</td>
            <td>{{ row.revenue ?? '—' }}</td>
            <td>{{ row.occupancy ?? '—' }}</td>
          </tr>
        </DataTableShell>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardItem>
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template #append>
              <BaseButton
                size="small"
                :variant="panelMode === 'filter' ? 'flat' : 'tonal'"
                :color="panelMode === 'filter' ? 'primary' : undefined"
                label="Filter"
                prepend-icon="bx-filter-alt"
                @click="openPanel('filter')"
              />
            </template>
          </VCardItem>

          <VCardText>
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of monthly closing metrics
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Rows
                    </div>
                    <div class="text-h5">
                      {{ stats.total }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Years
                    </div>
                    <div class="text-h5">
                      {{ stats.years }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Total revenue
                    </div>
                    <div class="text-h5">
                      {{ stats.totalRevenue.toLocaleString(undefined, { maximumFractionDigits: 0 }) }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Occupancy coverage
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!entities.loading"
                    title="No history yet"
                    description="Counts and charts will appear once metrics are loaded."
                  />
                </div>
              </ClientOnly>
            </div>

            <div v-else>
              <BaseInput
                v-model="filters.property"
                label="Property contains"
                class="mb-2"
              />
              <BaseInput
                v-model="filters.year"
                label="Year"
                class="mb-2"
              />
              <div class="d-flex flex-wrap gap-2 mt-2">
                <BaseButton
                  type="button"
                  variant="tonal"
                  label="Clear filters"
                  @click="clearFilters"
                />
                <BaseButton
                  type="button"
                  variant="text"
                  label="Back to overview"
                  @click="panelMode = 'dashboard'"
                />
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.entity-stat-tile {
  border: 1px solid rgba(75, 70, 92, 0.08);
  background: rgba(75, 70, 92, 0.03);
}

.entity-stat-tile--success {
  background: rgba(40, 199, 111, 0.08);
}

.entity-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.entity-stat-tile--info {
  background: rgba(0, 207, 232, 0.1);
}
</style>
