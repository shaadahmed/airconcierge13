<script setup>
import {
  AIRBNB_TOT_MODE_OPTIONS,
  LISTING_PLATFORM_OPTIONS,
  MANAGEMENT_TYPE_OPTIONS,
} from '@/constants/properties'
import {
  bookingLocation,
  bookingNightlyRateLabel,
  bookingPropertyTitle,
  bookingStatusColor,
  bookingStatusLabel,
} from '@/mocks/bookings'

const bookings = useBookingsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail'>} */
const panelMode = ref('dashboard')

const currentYear = new Date().getFullYear()
const currentMonth = new Date().getMonth() + 1

const defaultFilters = () => ({
  apply_colors: true,
  view_deleted: false,
  booking_code: '',
  region_id: null,
  year: currentYear,
  month: currentMonth,
  reservation_from: '',
  reservation_to: '',
  property_id: null,
  guest_id: null,
  subregion_id: null,
  booking_from: '',
  booking_to: '',
  no_of_nights: '',
  cancelled_booking: '9',
  management_type_id: null,
  platform_ids: [],
  tot_mode: null,
  cleaner_invoiced: null,
  dispute_status: null,
  include_owner_block: 1,
})

const filters = reactive(defaultFilters())

/** List toolbar quick search (separate from advance-search fields). */
const listSearch = ref('')

const selectedId = ref(null)
const confirmDelete = reactive({ open: false, id: null })
const confirmCancel = reactive({ open: false, id: null })
const panelBodyRef = ref(null)

const allRows = computed(() => {
  const data = bookings.data?.data || bookings.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const codeQuery = filters.booking_code.trim().toLowerCase()
  const propertyQuery = listSearch.value.trim().toLowerCase()

  return allRows.value.filter(booking => {
    if (codeQuery) {
      const code = String(booking.booking_code || booking.id || '').toLowerCase()
      const guest = String(booking.guest_name || '').toLowerCase()
      if (!code.includes(codeQuery) && !guest.includes(codeQuery))
        return false
    }

    if (propertyQuery) {
      const title = String(bookingPropertyTitle(booking)).toLowerCase()
      const location = String(bookingLocation(booking)).toLowerCase()
      if (!title.includes(propertyQuery) && !location.includes(propertyQuery))
        return false
    }

    return true
  })
})

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

const yearFilterItems = [
  { title: 'All', value: 0 },
  ...Array.from({ length: currentYear - 2014 + 2 }, (_, index) => {
    const year = 2014 + index

    return { title: String(year), value: year }
  }).reverse(),
]

const monthFilterItems = [
  { title: 'All', value: 0 },
  ...monthNames.map((name, index) => ({
    title: `${String(index + 1).padStart(2, '0')} ${name}`,
    value: index + 1,
  })),
]

const regionFilterItems = [
  { title: 'Select', value: null },
]

const subregionFilterItems = [
  { title: 'All', value: null },
]

const propertyFilterItems = computed(() => {
  const seen = new Map()

  for (const booking of allRows.value) {
    const id = booking.property_id ?? booking.property?.id
    if (id == null || seen.has(id))
      continue

    seen.set(id, bookingPropertyTitle(booking))
  }

  return [
    { title: 'Select', value: null },
    ...[...seen.entries()].map(([value, title]) => ({ title, value })),
  ]
})

const guestFilterItems = computed(() => {
  const seen = new Map()

  for (const booking of allRows.value) {
    const name = String(booking.guest_name || '').trim()
    if (!name || seen.has(name))
      continue

    seen.set(name, name)
  }

  return [
    { title: 'Select', value: null },
    ...[...seen.keys()].map(name => ({ title: name, value: name })),
  ]
})

const bookingTypeFilterItems = [
  { title: 'All', value: '' },
  { title: 'All bookings with a Payout (active or cancelled)', value: '9' },
  { title: 'Not Cancelled', value: '0' },
  { title: 'Cancelled with no payout', value: '1' },
  { title: 'Canceled with Payout (financials are being included in fees and Payouts)', value: '2' },
  { title: 'Cancelled with and without payout', value: '12' },
  { title: 'Disputed bookings', value: '10' },
]

const managementTypeFilterItems = [
  { title: 'All types', value: null },
  ...MANAGEMENT_TYPE_OPTIONS,
]

const platformFilterItems = LISTING_PLATFORM_OPTIONS

const totModeFilterItems = [
  { title: 'Select', value: null },
  ...AIRBNB_TOT_MODE_OPTIONS,
]

const cleanerInvoicedFilterItems = [
  { title: 'Yes/No/All', value: null },
  { title: 'Yes', value: '1' },
  { title: 'No', value: '0' },
]

const disputeStatusFilterItems = [
  { title: 'All', value: null },
  { title: 'No Dispute', value: '1' },
  { title: 'Flagged as Dispute', value: '0' },
  { title: 'Dispute Resolved', value: '2' },
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

const nightCount = booking => {
  const start = parseDate(booking?.reservation_start_date)
  const end = parseDate(booking?.reservation_end_date)
  if (!start || !end)
    return null

  const nights = Math.round((end - start) / (1000 * 60 * 60 * 24))

  return nights > 0 ? nights : null
}

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(booking => bookingStatusLabel(booking) === 'Active')
  const pending = list.filter(booking => bookingStatusLabel(booking) === 'Pending')
  const cancelled = list.filter(booking => bookingStatusLabel(booking) === 'Cancelled')
  const thisMonth = list.filter(booking =>
    isInCurrentMonth(booking.reservation_start_date || booking.booking_date || booking.created_at),
  )
  const withGuests = list.filter(booking => Number(booking.no_of_guests) > 0)

  return {
    total: list.length,
    active: active.length,
    pending: pending.length,
    cancelled: cancelled.length,
    thisMonth: thisMonth.length,
    withGuests: withGuests.length,
  }
})

const statusChartSeries = computed(() => [stats.value.active, stats.value.pending, stats.value.cancelled])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Active', 'Pending', 'Cancelled'],
  colors: ['#28c76f', '#00cfe8', '#ea5455'],
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

const selectedBooking = computed(() => {
  if (selectedId.value == null)
    return null

  return allRows.value.find(booking => String(booking.id) === String(selectedId.value)) || null
})

/** Detail mode: list on left (60%), minimal detail on right (40%). Overview/filter stay on the left. */
const isDetailLayout = computed(() => panelMode.value === 'detail' && Boolean(selectedBooking.value))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter bookings'
  if (isDetailLayout.value)
    return 'Booking'

  return 'Booking overview'
})

const resetPanelScroll = () => {
  nextTick(() => {
    const el = panelBodyRef.value?.$el ?? panelBodyRef.value
    if (el && typeof el.scrollTop === 'number')
      el.scrollTop = 0
  })
}

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = selectedId.value != null ? 'detail' : 'dashboard'

    return
  }

  panelMode.value = mode
}

const selectBooking = booking => {
  selectedId.value = booking.id
  panelMode.value = 'detail'
  resetPanelScroll()
}

const closeDetail = () => {
  selectedId.value = null
  panelMode.value = 'dashboard'
}

const clearFilters = () => {
  Object.assign(filters, defaultFilters())
}

const applyFilters = () => {
  panelMode.value = selectedId.value ? 'detail' : 'dashboard'
}

const goView = id => navigateTo(`/admin/bookings/${id}`)
const goEdit = id => navigateTo(`/admin/bookings/${id}`)
const goCreate = () => navigateTo('/admin/bookings/create')

const askDelete = id => {
  confirmDelete.open = true
  confirmDelete.id = id
}

const askCancel = id => {
  confirmCancel.open = true
  confirmCancel.id = id
}

const onDelete = async () => {
  try {
    const deletedId = confirmDelete.id
    await bookings.remove(deletedId)
    confirmDelete.open = false
    if (String(selectedId.value) === String(deletedId))
      closeDetail()
    if (!bookings.usingMocks)
      await bookings.load()
  }
  catch {
    // Store exposes errors.
  }
}

const onCancelBooking = async () => {
  try {
    const cancelledId = confirmCancel.id
    await bookings.cancel(cancelledId)
    confirmCancel.open = false
    if (!bookings.usingMocks)
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
  <div class="bookings-page">
    <PageHeader
      class="flex-shrink-0"
      title="Bookings"
      subtitle="Reservations across properties"
    >
      <template #actions>
        <BaseButton
          color="primary"
          label="Create booking"
          prepend-icon="bx-plus"
          @click="goCreate"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="bookings.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4 bookings-page__alert"
    >
      Showing UI preview data until the bookings API is connected.
    </VAlert>

    <div
      class="bookings-page__stage"
      :class="{ 'bookings-page__stage--detail': isDetailLayout }"
    >
      <!-- Side panel: overview/filter on left; minimal detail slides to the right when a booking is selected -->
      <div class="bookings-page__col bookings-page__col--side">
        <VCard class="booking-panel-card">
          <VCardItem class="flex-shrink-0">
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template #append>
              <div class="d-flex flex-wrap gap-2 align-center">
                <BaseButton
                  v-if="!isDetailLayout"
                  size="small"
                  :variant="panelMode === 'filter' ? 'flat' : 'tonal'"
                  :color="panelMode === 'filter' ? 'primary' : undefined"
                  label="Filter"
                  prepend-icon="bx-filter-alt"
                  @click="openPanel('filter')"
                />
                <VBtn
                  v-if="isDetailLayout"
                  icon
                  variant="text"
                  size="small"
                  aria-label="Close booking details"
                  @click="closeDetail"
                >
                  <VIcon icon="bx-x" />
                </VBtn>
              </div>
            </template>
          </VCardItem>

          <VCardText
            ref="panelBodyRef"
            class="booking-panel-body"
          >
            <Transition
              name="booking-panel-fade"
              mode="out-in"
            >
              <div
                v-if="isDetailLayout && selectedBooking"
                :key="`detail-${selectedBooking.id}`"
                class="booking-detail-minimal"
              >
              <div class="d-flex align-center gap-3 mb-4">
                <VAvatar
                  size="56"
                  rounded="lg"
                  color="primary"
                  variant="tonal"
                >
                  <VImg
                    v-if="selectedBooking.property?.property_image_url"
                    :src="selectedBooking.property.property_image_url"
                    cover
                  />
                  <VIcon
                    v-else
                    icon="bx-calendar-check"
                    size="26"
                  />
                </VAvatar>
                <div class="min-w-0">
                  <div class="d-flex align-center flex-wrap gap-2 mb-1">
                    <h2 class="text-h6 mb-0 text-truncate">
                      {{ bookingPropertyTitle(selectedBooking) }}
                    </h2>
                    <VChip
                      size="x-small"
                      :color="bookingStatusColor(selectedBooking)"
                      label
                    >
                      {{ bookingStatusLabel(selectedBooking) }}
                    </VChip>
                  </div>
                  <p class="text-body-2 text-medium-emphasis mb-0">
                    {{ bookingLocation(selectedBooking) }}
                  </p>
                </div>
              </div>

              <div class="d-flex flex-column gap-2 mb-5">
                <div class="d-flex align-center gap-2 text-body-2">
                  <VIcon
                    icon="bx-hash"
                    size="18"
                    class="text-medium-emphasis"
                  />
                  <span>{{ selectedBooking.booking_code || selectedBooking.id }}</span>
                </div>
                <div class="d-flex align-center gap-2 text-body-2">
                  <VIcon
                    icon="bx-calendar"
                    size="18"
                    class="text-medium-emphasis"
                  />
                  <span>
                    {{ formatDate(selectedBooking.reservation_start_date) }}
                    –
                    {{ formatDate(selectedBooking.reservation_end_date) }}
                    <template v-if="nightCount(selectedBooking)">
                      ({{ nightCount(selectedBooking) }} nights)
                    </template>
                  </span>
                </div>
                <div class="d-flex align-center gap-2 text-body-2">
                  <VIcon
                    icon="bx-user"
                    size="18"
                    class="text-medium-emphasis"
                  />
                  <span>
                    {{ selectedBooking.guest_name || '—' }}
                    <template v-if="selectedBooking.no_of_guests">
                      · {{ selectedBooking.no_of_guests }} guests
                    </template>
                  </span>
                </div>
                <div class="d-flex align-center gap-2 text-body-2">
                  <VIcon
                    icon="bx-dollar"
                    size="18"
                    class="text-medium-emphasis"
                  />
                  <span>{{ bookingNightlyRateLabel(selectedBooking) }}</span>
                </div>
                <div class="d-flex align-center gap-2 text-body-2">
                  <VIcon
                    icon="bx-globe"
                    size="18"
                    class="text-medium-emphasis"
                  />
                  <span>{{ selectedBooking.platform || '—' }}</span>
                </div>
              </div>

              <VRow dense>
                <VCol cols="6">
                  <BaseButton
                    color="primary"
                    block
                    label="View booking"
                    prepend-icon="bx-show"
                    @click="goView(selectedBooking.id)"
                  />
                </VCol>
                <VCol cols="6">
                  <BaseButton
                    variant="tonal"
                    block
                    label="Edit booking"
                    prepend-icon="bx-edit"
                    @click="goEdit(selectedBooking.id)"
                  />
                </VCol>
                <VCol
                  v-if="bookingStatusLabel(selectedBooking) !== 'Cancelled'"
                  cols="6"
                >
                  <BaseButton
                    variant="tonal"
                    color="warning"
                    block
                    label="Cancel booking"
                    prepend-icon="bx-calendar-x"
                    @click="askCancel(selectedBooking.id)"
                  />
                </VCol>
                <VCol cols="6">
                  <BaseButton
                    variant="tonal"
                    color="error"
                    block
                    label="Delete"
                    prepend-icon="bx-trash"
                    @click="askDelete(selectedBooking.id)"
                  />
                </VCol>
              </VRow>
              </div>

              <div
                v-else-if="panelMode === 'filter'"
                key="filter"
              >
              <VForm @submit.prevent="applyFilters">
                <div class="d-flex flex-wrap gap-x-4 gap-y-2 mb-4">
                  <BaseCheckbox
                    v-model="filters.apply_colors"
                    label="Apply Colors"
                    hide-details
                  />
                  <BaseCheckbox
                    v-model="filters.view_deleted"
                    label="View Deleted Entries"
                    hide-details
                  />
                </div>

                <VRow>
                  <VCol cols="12">
                    <BaseInput
                      v-model="filters.booking_code"
                      label="Booking Code"
                      placeholder="e.g., BK123456"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.region_id"
                      label="City/Region"
                      placeholder="Select"
                      :items="regionFilterItems"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.subregion_id"
                      label="Subregion"
                      :items="subregionFilterItems"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.year"
                      label="Year"
                      :items="yearFilterItems"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.month"
                      label="Month"
                      :items="monthFilterItems"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.property_id"
                      label="Property"
                      placeholder="Select"
                      :items="propertyFilterItems"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.guest_id"
                      label="Guest"
                      placeholder="Select"
                      :items="guestFilterItems"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseInput
                      v-model="filters.reservation_from"
                      label="Reservation From"
                      type="date"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseInput
                      v-model="filters.reservation_to"
                      label="Reservation To"
                      type="date"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseInput
                      v-model="filters.booking_from"
                      label="Booking From"
                      type="date"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseInput
                      v-model="filters.booking_to"
                      label="Booking To"
                      type="date"
                    />
                  </VCol>

                  <VCol cols="12">
                    <BaseInput
                      v-model="filters.no_of_nights"
                      label="Number of Nights"
                      placeholder="Min/Max nights e.g., 3-7"
                    />
                  </VCol>

                  <VCol cols="12">
                    <BaseSelect
                      v-model="filters.cancelled_booking"
                      label="Booking Type (Cancellation)"
                      :items="bookingTypeFilterItems"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.management_type_id"
                      label="Management Type"
                      :items="managementTypeFilterItems"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.platform_ids"
                      label="Platform(s)"
                      placeholder="Select"
                      :items="platformFilterItems"
                      multiple
                      chips
                      closable-chips
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.tot_mode"
                      label="TOT Mode"
                      placeholder="Select"
                      :items="totModeFilterItems"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.cleaner_invoiced"
                      label="Cleaner Invoiced"
                      :items="cleanerInvoicedFilterItems"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filters.dispute_status"
                      label="Dispute Status"
                      :items="disputeStatusFilterItems"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <div class="text-body-2 mb-1">
                      Include Owner Block
                    </div>
                    <VRadioGroup
                      v-model="filters.include_owner_block"
                      inline
                      hide-details="auto"
                    >
                      <BaseRadio
                        :value="1"
                        label="Yes"
                      />
                      <BaseRadio
                        :value="0"
                        label="No"
                      />
                    </VRadioGroup>
                  </VCol>
                </VRow>

                <div class="d-flex flex-wrap gap-2 mt-4">
                  <BaseButton
                    type="button"
                    variant="tonal"
                    label="Clear All"
                    @click="clearFilters"
                  />
                  <BaseButton
                    type="submit"
                    color="primary"
                    label="Apply Filters"
                  />
                  <BaseButton
                    type="button"
                    variant="text"
                    label="Back to overview"
                    @click="panelMode = selectedId ? 'detail' : 'dashboard'"
                  />
                </div>
              </VForm>
              </div>

              <div
                v-else
                key="dashboard"
              >
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
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Pending
                    </div>
                    <div class="text-h5">
                      {{ stats.pending }}
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
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Status mix
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
                Select a booking to open a minimal summary on the right. View, edit, and create use separate pages.
              </VAlert>
              </div>
            </Transition>
          </VCardText>
        </VCard>
      </div>

      <!-- List: right for overview/filter (60%); slides left when a booking is selected -->
      <div class="bookings-page__col bookings-page__col--list">
        <VCard class="booking-list-card">
          <VCardItem class="flex-shrink-0">
            <VCardTitle>All bookings</VCardTitle>
            <template
              v-if="isDetailLayout"
              #append
            >
              <BaseButton
                size="small"
                variant="tonal"
                label="Filter"
                prepend-icon="bx-filter-alt"
                @click="openPanel('filter')"
              />
            </template>
          </VCardItem>

          <VCardText class="pb-2 flex-shrink-0">
            <BaseInput
              v-model="listSearch"
              placeholder="Search properties..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            v-if="bookings.loading"
            indeterminate
            class="flex-shrink-0"
          />

          <div class="booking-list">
            <button
              v-for="booking in rows"
              :key="booking.id"
              type="button"
              class="booking-list-item"
              :class="{ 'booking-list-item--selected': String(selectedId) === String(booking.id) }"
              @click="selectBooking(booking)"
            >
              <VAvatar
                size="48"
                rounded="lg"
                class="booking-list-item__thumb"
                color="primary"
                variant="tonal"
              >
                <VImg
                  v-if="booking.property?.property_image_url"
                  :src="booking.property.property_image_url"
                  cover
                />
                <VIcon
                  v-else
                  icon="bx-calendar-check"
                  size="22"
                />
              </VAvatar>

              <div class="booking-list-item__body">
                <div class="booking-list-item__title text-truncate">
                  {{ bookingPropertyTitle(booking) }}
                </div>
                <div class="booking-list-item__subtitle text-truncate">
                  {{ bookingLocation(booking) }}
                </div>
              </div>

              <div class="booking-list-item__meta">
                <VChip
                  size="x-small"
                  :color="bookingStatusColor(booking)"
                  label
                  class="booking-list-item__status"
                >
                  {{ bookingStatusLabel(booking) }}
                </VChip>
                <div class="booking-list-item__price">
                  {{ bookingNightlyRateLabel(booking) }}
                </div>
              </div>

              <VIcon
                icon="bx-chevron-right"
                size="20"
                class="booking-list-item__chevron"
              />
            </button>

            <EmptyState
              v-if="!bookings.loading && rows.length === 0"
              title="No bookings found"
              :description="allRows.length && rows.length === 0 ? 'No bookings match the current filters.' : ''"
            />
          </div>
        </VCard>
      </div>
    </div>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete booking?"
      message="This removes the booking from the list (UI preview only for now)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="bookings.loading"
      @confirm="onDelete"
    />

    <ConfirmDialog
      v-model="confirmCancel.open"
      title="Cancel booking?"
      message="This marks the reservation as cancelled. You can still delete it later."
      confirm-label="Cancel booking"
      confirm-color="warning"
      :loading="bookings.loading"
      @confirm="onCancelBooking"
    />
  </div>
</template>

<style scoped>
/* Navbar (64px) + layout-page-content padding-block (1.5rem × 2) */
.bookings-page {
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.bookings-page__alert {
  flex: 0 0 auto;
}

.bookings-page__stage {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  min-height: 0;
}

@media (min-width: 960px) {
  .bookings-page {
    height: calc(100dvh - 64px - 3rem);
    overflow: hidden;
  }

  .bookings-page__stage {
    position: relative;
    flex: 1 1 0;
    flex-direction: row;
    gap: 0;
    min-height: 0;
    overflow: hidden;
  }

  .bookings-page__col {
    position: absolute;
    top: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    min-height: 0;
    max-height: 100%;
    transition:
      left 0.45s cubic-bezier(0.22, 1, 0.36, 1),
      right 0.45s cubic-bezier(0.22, 1, 0.36, 1),
      padding 0.45s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: left;
  }

  .booking-list-card,
  .booking-panel-card {
    flex: 1 1 auto;
    height: 100%;
    min-height: 0;
    max-height: 100%;
  }

  /* Overview/filter: side 40% left, list 60% right */
  .bookings-page__col--side {
    width: calc(41.6667% - 0.5rem);
    left: 0;
  }

  .bookings-page__col--list {
    width: calc(58.3333% - 0.5rem);
    left: calc(41.6667% + 0.5rem);
  }

  /* Detail: list slides left, side slides right */
  .bookings-page__stage--detail .bookings-page__col--list {
    left: 0;
  }

  .bookings-page__stage--detail .bookings-page__col--side {
    left: calc(58.3333% + 0.5rem);
  }
}

@media (min-width: 960px) and (prefers-reduced-motion: reduce) {
  .bookings-page__col {
    transition: none;
  }
}

.booking-list-card,
.booking-panel-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.booking-panel-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
}

.booking-list {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  padding: 0 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.booking-list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  text-align: left;
  cursor: pointer;
  transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.booking-list-item:hover {
  background: rgba(105, 108, 255, 0.04);
  border-color: rgba(105, 108, 255, 0.28);
}

.booking-list-item--selected {
  background: rgba(105, 108, 255, 0.1);
  border-color: rgba(105, 108, 255, 0.45);
  box-shadow: 0 0 0 1px rgba(105, 108, 255, 0.12);
}

.booking-list-item__thumb {
  flex-shrink: 0;
}

.booking-list-item__body {
  flex: 1;
  min-width: 0;
}

.booking-list-item__title {
  font-weight: 600;
  font-size: 0.9375rem;
  line-height: 1.3;
  color: rgb(var(--v-theme-on-surface));
}

.booking-list-item__subtitle {
  margin-top: 2px;
  font-size: 0.8125rem;
  line-height: 1.3;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.booking-list-item__meta {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  min-width: 5.5rem;
}

.booking-list-item__price {
  font-size: 0.8125rem;
  font-weight: 500;
  line-height: 1.3;
  color: rgba(var(--v-theme-on-surface), 0.75);
  white-space: nowrap;
}

.booking-list-item__chevron {
  flex-shrink: 0;
  color: rgba(var(--v-theme-on-surface), 0.38);
}

.booking-list-item--selected .booking-list-item__chevron {
  color: rgb(var(--v-theme-primary));
}

.booking-detail-minimal {
  max-width: 28rem;
}

.booking-panel-fade-enter-active,
.booking-panel-fade-leave-active {
  transition:
    opacity 0.22s ease,
    transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
}

.booking-panel-fade-enter-from {
  opacity: 0;
  transform: translateX(16px);
}

.booking-panel-fade-leave-to {
  opacity: 0;
  transform: translateX(-12px);
}

@media (prefers-reduced-motion: reduce) {
  .booking-panel-fade-enter-active,
  .booking-panel-fade-leave-active {
    transition: none;
  }

  .booking-panel-fade-enter-from,
  .booking-panel-fade-leave-to {
    transform: none;
  }
}

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
