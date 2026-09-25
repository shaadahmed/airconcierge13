<script setup>
const audits = usePropertyAuditsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter'>} */
const panelMode = ref('dashboard')

const filters = reactive({
  title: '',
  status: null,
})

const allRows = computed(() => {
  const data = audits.data?.data || audits.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const titleQuery = filters.title.trim().toLowerCase()

  return allRows.value.filter(row => {
    if (titleQuery) {
      const title = String(row.property_title || '').toLowerCase()
      if (!title.includes(titleQuery))
        return false
    }

    if (filters.status !== null && filters.status !== undefined && filters.status !== '') {
      const isActive = Boolean(row.status)
      if (filters.status === 'active' && !isActive)
        return false
      if (filters.status === 'inactive' && isActive)
        return false
    }

    return true
  })
})

const statusFilterItems = [
  { title: 'All statuses', value: null },
  { title: 'Active / live', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
]

const monthBounds = computed(() => {
  const now = new Date()
  const start = new Date(now.getFullYear(), now.getMonth(), 1)
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999)

  return { start, end, label: start.toLocaleString(undefined, { month: 'long', year: 'numeric' }) }
})

const parseDate = value => {
  if (!value)
    return null

  const date = new Date(value)

  return Number.isNaN(date.getTime()) ? null : date
}

const isInCurrentMonth = value => {
  const date = parseDate(value)
  if (!date)
    return false

  const { start, end } = monthBounds.value

  return date >= start && date <= end
}

const formatDate = value => {
  const date = parseDate(value)
  if (!date)
    return '—'

  return date.toLocaleDateString()
}

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(row => Boolean(row.status))
  const inactive = list.filter(row => !row.status)
  const updatedThisMonth = list.filter(row => isInCurrentMonth(row.updated_at))

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    updatedThisMonth: updatedThisMonth.length,
  }
})

const statusChartSeries = computed(() => [stats.value.active, stats.value.inactive])

const statusChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  labels: ['Active', 'Inactive'],
  colors: ['#28c76f', '#a8aaae'],
  legend: {
    position: 'bottom',
    fontSize: '13px',
  },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          name: { show: true, fontSize: '13px' },
          value: {
            show: true,
            fontSize: '22px',
            fontWeight: 600,
            formatter: value => String(value),
          },
          total: {
            show: true,
            label: 'Total',
            fontSize: '13px',
            formatter: () => String(stats.value.total),
          },
        },
      },
    },
  },
  stroke: { width: 0 },
  tooltip: {
    y: { formatter: value => `${value} properties` },
  },
}))

const activityChartSeries = computed(() => ([
  {
    name: 'Properties',
    data: [
      stats.value.total,
      stats.value.updatedThisMonth,
      stats.value.inactive,
    ],
  },
]))

const activityChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  plotOptions: {
    bar: {
      borderRadius: 6,
      columnWidth: '48%',
      distributed: true,
    },
  },
  colors: ['#696cff', '#00cfe8', '#ff9f43'],
  dataLabels: { enabled: false },
  legend: { show: false },
  grid: {
    strokeDashArray: 6,
    borderColor: 'rgba(75, 70, 92, 0.12)',
    yaxis: { lines: { show: true } },
    xaxis: { lines: { show: false } },
  },
  xaxis: {
    categories: ['Listed', 'Updated', 'Inactive'],
    labels: { style: { colors: '#a5a3ae', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: { colors: '#a5a3ae' },
      formatter: value => Math.round(value),
    },
    min: 0,
    forceNiceScale: true,
  },
  tooltip: {
    y: { formatter: value => `${value} properties` },
  },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter audit reports'

  return 'Audit overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = 'dashboard'

    return
  }

  panelMode.value = mode
}

const clearFilters = () => {
  Object.assign(filters, {
    title: '',
    status: null,
  })
}

onMounted(() => audits.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Audit Reports"
      subtitle="Property visit and audit reports"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All audit reports"
          :loading="audits.loading"
          :empty="rows.length === 0"
          :empty-colspan="4"
          empty-title="No audit reports found"
          :empty-description="allRows.length && rows.length === 0 ? 'No reports match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Property</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="row in rows"
            :key="row.id"
          >
            <td>{{ row.property_title || '—' }}</td>
            <td>{{ row.status ? 'Live' : 'Inactive' }}</td>
            <td>{{ formatDate(row.updated_at) }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Create visit"
                :to="`/admin/properties/audit/create?property=${row.id}`"
              />
            </td>
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
              <div class="d-flex flex-wrap gap-2">
                <BaseButton
                  size="small"
                  :variant="panelMode === 'filter' ? 'flat' : 'tonal'"
                  :color="panelMode === 'filter' ? 'primary' : undefined"
                  label="Filter"
                  prepend-icon="bx-filter-alt"
                  @click="openPanel('filter')"
                />
                <BaseButton
                  size="small"
                  variant="tonal"
                  color="primary"
                  label="Create"
                  prepend-icon="bx-plus"
                  to="/admin/properties/audit/create"
                />
              </div>
            </template>
          </VCardItem>

          <VCardText>
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot for {{ monthBounds.label }}
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Active
                    </div>
                    <div class="text-h5">
                      {{ stats.active }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Inactive
                    </div>
                    <div class="text-h5">
                      {{ stats.inactive }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Listed
                    </div>
                    <div class="text-h5">
                      {{ stats.total }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Updated this month
                    </div>
                    <div class="text-h5">
                      {{ stats.updatedThisMonth }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Active vs inactive
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!audits.loading"
                    title="No reports yet"
                    description="Counts and charts will appear once audit data is loaded."
                  />
                </div>

                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    This month
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="bar"
                    height="200"
                    :options="activityChartOptions"
                    :series="activityChartSeries"
                  />
                </div>
              </ClientOnly>

              <VAlert
                type="info"
                variant="tonal"
                density="compact"
                class="mt-4"
              >
                Create opens a dedicated visit report page instead of this panel.
              </VAlert>
            </div>

            <div v-else>
              <VForm @submit.prevent>
                <BaseInput
                  v-model="filters.title"
                  label="Property title contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.status"
                  label="Status"
                  :items="statusFilterItems"
                  clearable
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
              </VForm>
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

.entity-stat-tile--muted {
  background: rgba(168, 170, 174, 0.12);
}

.entity-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.entity-stat-tile--warning {
  background: rgba(255, 159, 67, 0.1);
}
</style>
