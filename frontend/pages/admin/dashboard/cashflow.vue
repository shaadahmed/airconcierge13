<script setup>
const entities = useAdminEntitiesStore()
const endpoint = '/admin/dashboard/cashflow'

/** @type {import('vue').Ref<'dashboard' | 'filter'>} */
const panelMode = ref('dashboard')

const filters = reactive({
  period: '',
})

const allRows = computed(() => entities.records[endpoint] || [])

const periodLabel = row => row.month || row.period || '—'

const rows = computed(() => {
  const periodQuery = filters.period.trim().toLowerCase()

  return allRows.value.filter(row => {
    if (!periodQuery)
      return true

    return String(periodLabel(row)).toLowerCase().includes(periodQuery)
  })
})

const amount = row => Number(row.total ?? row.net_income ?? row.income ?? 0)

const stats = computed(() => {
  const list = allRows.value
  const total = list.reduce((sum, row) => sum + amount(row), 0)
  const positive = list.filter(row => amount(row) > 0)
  const zero = list.filter(row => amount(row) === 0)

  return {
    periods: list.length,
    total,
    positive: positive.length,
    zero: zero.length,
    average: list.length ? total / list.length : 0,
  }
})

const statusChartSeries = computed(() => [stats.value.positive, stats.value.zero])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Positive', 'Zero'],
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
          total: { show: true, label: 'Periods', fontSize: '13px', formatter: () => String(stats.value.periods) },
        },
      },
    },
  },
  stroke: { width: 0 },
  tooltip: { y: { formatter: value => `${value} periods` } },
}))

const activityChartSeries = computed(() => ([{
  name: 'Cash flow',
  data: rows.value.slice(-6).map(row => amount(row)),
}]))

const activityChartOptions = computed(() => ({
  chart: { type: 'bar', parentHeightOffset: 0, toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '48%' } },
  colors: ['#696cff'],
  dataLabels: { enabled: false },
  legend: { show: false },
  grid: {
    strokeDashArray: 6,
    borderColor: 'rgba(75, 70, 92, 0.12)',
    yaxis: { lines: { show: true } },
    xaxis: { lines: { show: false } },
  },
  xaxis: {
    categories: rows.value.slice(-6).map(row => periodLabel(row)),
    labels: { style: { colors: '#a5a3ae', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: { style: { colors: '#a5a3ae' }, formatter: value => Math.round(value) },
    forceNiceScale: true,
  },
  tooltip: { y: { formatter: value => String(value) } },
}))

const panelTitle = computed(() => panelMode.value === 'filter' ? 'Filter cash flow' : 'Cash flow overview')

const openPanel = mode => {
  panelMode.value = panelMode.value === mode ? 'dashboard' : mode
}

const clearFilters = () => {
  filters.period = ''
}

onMounted(() => entities.list(endpoint))

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Cash Flow"
      subtitle="Monthly payment totals"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="Cash flow periods"
          :loading="entities.loading"
          :errors="entities.errors"
          :empty="rows.length === 0"
          :empty-colspan="2"
          empty-title="No cash flow data found"
          :empty-description="allRows.length && rows.length === 0 ? 'No periods match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Period</th>
                <th>Total</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="(row, index) in rows"
            :key="periodLabel(row) + index"
          >
            <td>{{ periodLabel(row) }}</td>
            <td>{{ amount(row).toLocaleString(undefined, { maximumFractionDigits: 2 }) }}</td>
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
                Snapshot of monthly payment totals
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Periods
                    </div>
                    <div class="text-h5">
                      {{ stats.periods }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Positive
                    </div>
                    <div class="text-h5">
                      {{ stats.positive }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Total
                    </div>
                    <div class="text-h5">
                      {{ stats.total.toLocaleString(undefined, { maximumFractionDigits: 0 }) }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Period mix
                  </div>
                  <VueApexCharts
                    v-if="stats.periods > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!entities.loading"
                    title="No cash flow yet"
                    description="Counts and charts will appear once payment totals are loaded."
                  />
                </div>
                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    Recent periods
                  </div>
                  <VueApexCharts
                    v-if="stats.periods > 0"
                    type="bar"
                    height="200"
                    :options="activityChartOptions"
                    :series="activityChartSeries"
                  />
                </div>
              </ClientOnly>
            </div>

            <div v-else>
              <BaseInput
                v-model="filters.period"
                label="Period contains"
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

.entity-stat-tile--warning {
  background: rgba(255, 159, 67, 0.1);
}
</style>
