<script setup>
const bookings = useBookingsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter'>} */
const panelMode = ref('dashboard')

const filters = reactive({
  code: '',
  property: '',
  cancelled: null,
})

const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = bookings.data?.data || bookings.data || []

  return Array.isArray(data) ? data : []
})

const propertyTitle = booking => booking.property?.property_title || booking.property?.name || booking.property_id || '—'

const rows = computed(() => {
  const codeQuery = filters.code.trim().toLowerCase()
  const propertyQuery = filters.property.trim().toLowerCase()

  return allRows.value.filter(booking => {
    if (codeQuery) {
      const code = String(booking.booking_code || booking.id || '').toLowerCase()
      if (!code.includes(codeQuery))
        return false
    }

    if (propertyQuery) {
      const title = String(propertyTitle(booking)).toLowerCase()
      if (!title.includes(propertyQuery))
        return false
    }

    if (filters.cancelled !== null && filters.cancelled !== undefined && filters.cancelled !== '') {
      const isCancelled = Boolean(booking.cancelled_booking)
      if (filters.cancelled === 'yes' && !isCancelled)
        return false
      if (filters.cancelled === 'no' && isCancelled)
        return false
    }

    return true
  })
})

const cancelledFilterItems = [
  { title: 'All bookings', value: null },
  { title: 'Active', value: 'no' },
  { title: 'Cancelled', value: 'yes' },
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
  const active = list.filter(booking => !booking.cancelled_booking)
  const cancelled = list.filter(booking => Boolean(booking.cancelled_booking))
  const thisMonth = list.filter(booking =>
    isInCurrentMonth(booking.reservation_start_date || booking.booking_date || booking.dateadded),
  )
  const withGuests = list.filter(booking => Number(booking.no_of_guests) > 0)

  return {
    total: list.length,
    active: active.length,
    cancelled: cancelled.length,
    thisMonth: thisMonth.length,
    withGuests: withGuests.length,
  }
})

const statusChartSeries = computed(() => [stats.value.active, stats.value.cancelled])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Active', 'Cancelled'],
  colors: ['#28c76f', '#ea5455'],
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
  tooltip: { y: { formatter: value => `${value} bookings` } },
}))

const activityChartSeries = computed(() => ([{
  name: 'Bookings',
  data: [stats.value.thisMonth, stats.value.withGuests, stats.value.cancelled],
}]))

const activityChartOptions = computed(() => ({
  chart: { type: 'bar', parentHeightOffset: 0, toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
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
    categories: ['This month', 'With guests', 'Cancelled'],
    labels: { style: { colors: '#a5a3ae', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: { style: { colors: '#a5a3ae' }, formatter: value => Math.round(value) },
    min: 0,
    forceNiceScale: true,
  },
  tooltip: { y: { formatter: value => `${value} bookings` } },
}))

const panelTitle = computed(() => panelMode.value === 'filter' ? 'Filter bookings' : 'Booking overview')

const openPanel = mode => {
  panelMode.value = panelMode.value === mode ? 'dashboard' : mode
}

const clearFilters = () => {
  Object.assign(filters, { code: '', property: '', cancelled: null })
}

const askDelete = id => {
  confirmDelete.open = true
  confirmDelete.id = id
}

const onDelete = async () => {
  try {
    await bookings.remove(confirmDelete.id)
    confirmDelete.open = false
    await bookings.load()
  }
  catch {
    // Store exposes errors.
  }
}

onMounted(() => bookings.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Bookings"
      subtitle="Reservations across properties"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All bookings"
          :loading="bookings.loading"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No bookings found"
          :empty-description="allRows.length && rows.length === 0 ? 'No bookings match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Booking</th>
                <th>Property</th>
                <th>Dates</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="booking in rows"
            :key="booking.id"
          >
            <td>{{ booking.booking_code || booking.id }}</td>
            <td>{{ propertyTitle(booking) }}</td>
            <td>
              {{ formatDate(booking.reservation_start_date) }}
              –
              {{ formatDate(booking.reservation_end_date) }}
            </td>
            <td>{{ booking.cancelled_booking ? 'Cancelled' : 'Active' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="View"
                class="me-2"
                :to="`/admin/bookings/${booking.id}`"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(booking.id)"
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
                  to="/admin/bookings/create"
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
                  <div class="entity-stat-tile entity-stat-tile--danger pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Cancelled
                    </div>
                    <div class="text-h5">
                      {{ stats.cancelled }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      This month
                    </div>
                    <div class="text-h5">
                      {{ stats.thisMonth }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With guests
                    </div>
                    <div class="text-h5">
                      {{ stats.withGuests }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Active vs cancelled
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!bookings.loading"
                    title="No bookings yet"
                    description="Counts and charts will appear once bookings are loaded."
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
                Create opens a dedicated booking page instead of this panel.
              </VAlert>
            </div>

            <div v-else>
              <VForm @submit.prevent>
                <BaseInput
                  v-model="filters.code"
                  label="Booking code contains"
                  class="mb-2"
                />
                <BaseInput
                  v-model="filters.property"
                  label="Property contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.cancelled"
                  label="Status"
                  :items="cancelledFilterItems"
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

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete booking?"
      message="This soft-deletes the booking (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="bookings.loading"
      @confirm="onDelete"
    />
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

.entity-stat-tile--danger {
  background: rgba(234, 84, 85, 0.1);
}

.entity-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.entity-stat-tile--info {
  background: rgba(0, 207, 232, 0.1);
}
</style>
