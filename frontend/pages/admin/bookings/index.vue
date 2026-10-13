<script setup>
import {
  BOOKING_CANCELLED_OPTIONS,
  BOOKING_DISPUTE_STATUS_OPTIONS,
} from '@/constants/bookings'
import {
  AIRBNB_TOT_MODE_OPTIONS,
  LISTING_PLATFORM_OPTIONS,
  MANAGEMENT_TYPE_OPTIONS,
} from '@/constants/properties'
import {
  bookingAcRevenueLabel,
  bookingGuestPaidLabel,
  bookingLocation,
  bookingNightlyRateLabel,
  bookingPlatformLabel,
  bookingPropertyTitle,
  bookingStatusColor,
  bookingStatusLabel,
  isCancelledBooking,
  isClearedBooking,
  isOwnerBlockBooking,
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
/** @type {{ open: boolean, id: string|number|null, type: 'cancel'|'resolve'|null, cancelled_booking: string|null, dispute_status: string|null }} */
const bookingActionModal = reactive({
  open: false,
  id: null,
  type: null,
  cancelled_booking: null,
  dispute_status: null,
})
const cancelledBookingOptions = BOOKING_CANCELLED_OPTIONS
const disputeStatusOptions = BOOKING_DISPUTE_STATUS_OPTIONS
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

/** Selected filter month/year for overview stats; "All" (0) falls back to the current month. */
const selectedMonthBounds = computed(() => {
  const year = Number(filters.year) > 0 ? Number(filters.year) : currentYear
  const month = Number(filters.month) > 0 ? Number(filters.month) : currentMonth
  const start = new Date(year, month - 1, 1)
  const end = new Date(year, month, 0, 23, 59, 59, 999)

  return {
    year,
    month,
    start,
    end,
    label: start.toLocaleString(undefined, { month: 'long', year: 'numeric' }),
  }
})

const parseDate = value => {
  if (!value)
    return null

  const date = new Date(value)

  return Number.isNaN(date.getTime()) ? null : date
}

const isInSelectedMonth = booking => {
  const { year, month, start, end } = selectedMonthBounds.value
  const bookingYear = Number(booking?.year)
  const bookingMonth = Number(booking?.month)

  if (Number.isFinite(bookingYear) && bookingYear > 0 && Number.isFinite(bookingMonth) && bookingMonth > 0)
    return bookingYear === year && bookingMonth === month

  const date = parseDate(booking?.reservation_start_date || booking?.booking_date || booking?.created_at)
  if (!date)
    return false

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
  const guestActive = list.filter(booking =>
    !isOwnerBlockBooking(booking)
    && !isCancelledBooking(booking)
    && bookingStatusLabel(booking) === 'Active',
  )
  const cancelled = list.filter(booking => isCancelledBooking(booking) && !isOwnerBlockBooking(booking))
  const cleared = list.filter(booking => isClearedBooking(booking) && isInSelectedMonth(booking))
  const ownerBlocks = list.filter(booking => isOwnerBlockBooking(booking))
  const chartTotal = guestActive.length + cancelled.length + cleared.length + ownerBlocks.length

  return {
    total: list.length,
    chartTotal,
    guestActive: guestActive.length,
    cancelled: cancelled.length,
    cleared: cleared.length,
    ownerBlocks: ownerBlocks.length,
  }
})

const clearedBookingsHint = computed(() =>
  `Cleared bookings mean cleared for payout in ${selectedMonthBounds.value.label}.`,
)

const statusChartSeries = computed(() => [
  stats.value.guestActive,
  stats.value.cancelled,
  stats.value.cleared,
  stats.value.ownerBlocks,
])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: [
    'Guest Bookings (Active)',
    'Cancelled Bookings',
    'Cleared Bookings',
    'Owner Blocks',
  ],
  colors: ['#6865FF', '#16A34A', '#DC3545', '#6c757d'],
  legend: {
    position: 'right',
    fontSize: '12px',
    offsetY: 24,
    markers: { width: 10, height: 10, radius: 10 },
    itemMargin: { vertical: 6 },
  },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      offsetX: -8,
      donut: {
        size: '68%',
        labels: {
          show: true,
          name: { show: true, fontSize: '13px' },
          value: { show: true, fontSize: '22px', fontWeight: 600, formatter: value => String(value) },
          total: { show: true, label: 'Total', fontSize: '13px', formatter: () => String(stats.value.chartTotal) },
        },
      },
    },
  },
  stroke: { width: 0 },
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
const goEdit = id => navigateTo(`/admin/bookings/${id}/edit`)
const goCreate = () => navigateTo('/admin/bookings/create')

const askDelete = id => {
  confirmDelete.open = true
  confirmDelete.id = id
}

const cancelledOptionValue = value => {
  if (value === true || value === 1)
    return '1'
  if (value === false || value == null || value === '')
    return '0'

  return String(value)
}

const disputeOptionValue = booking => {
  if (booking?.dispute_status != null && booking.dispute_status !== '')
    return String(booking.dispute_status)
  if (booking?.is_payment_clear != null && booking.is_payment_clear !== '')
    return String(booking.is_payment_clear)

  return '1'
}

const openBookingActionModal = (type, id) => {
  const booking = allRows.value.find(row => String(row.id) === String(id))
    || (String(selectedBooking.value?.id) === String(id) ? selectedBooking.value : null)

  bookingActionModal.type = type
  bookingActionModal.id = id
  bookingActionModal.cancelled_booking = type === 'cancel'
    ? '1'
    : cancelledOptionValue(booking?.cancelled_booking)
  bookingActionModal.dispute_status = type === 'resolve'
    ? '2'
    : disputeOptionValue(booking)
  bookingActionModal.open = true
}

const askCancel = id => openBookingActionModal('cancel', id)
const askResolveDispute = id => openBookingActionModal('resolve', id)

const closeBookingActionModal = () => {
  bookingActionModal.open = false
}

const resetBookingActionModal = () => {
  bookingActionModal.id = null
  bookingActionModal.type = null
  bookingActionModal.cancelled_booking = null
  bookingActionModal.dispute_status = null
}

const actionModalBooking = computed(() => {
  if (bookingActionModal.id == null)
    return null

  return allRows.value.find(booking => String(booking.id) === String(bookingActionModal.id))
    || selectedBooking.value
})

const actionModalInfoFields = computed(() => {
  const booking = actionModalBooking.value
  if (!booking)
    return []

  return [
    { label: 'Booking Code', value: booking.booking_code || String(booking.id) },
    { label: 'Status', value: bookingStatusLabel(booking) },
    { label: 'Property', value: bookingPropertyTitle(booking) },
    { label: 'Guest', value: booking.guest_name || '—' },
    { label: 'Platform', value: bookingPlatformLabel(booking) || '—' },
    { label: 'Reservation Start', value: formatDate(booking.reservation_start_date) },
    { label: 'Reservation End', value: formatDate(booking.reservation_end_date) },
  ]
})

const actionModalTitle = computed(() => (
  bookingActionModal.type === 'resolve' ? 'Resolve dispute' : 'Cancel booking'
))

const actionModalConfirmLabel = computed(() => (
  bookingActionModal.type === 'resolve' ? 'Resolve dispute' : 'Cancel booking'
))

const actionModalConfirmColor = computed(() => (
  bookingActionModal.type === 'resolve' ? 'primary' : 'warning'
))

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

const onConfirmBookingAction = async () => {
  try {
    if (bookingActionModal.id == null)
      return

    const payload = {
      cancelled_booking: bookingActionModal.cancelled_booking,
      dispute_status: bookingActionModal.dispute_status,
    }

    if (bookingActionModal.type === 'cancel') {
      await bookings.cancel(bookingActionModal.id, payload)
    }
    else if (bookingActionModal.type === 'resolve') {
      await bookings.save(bookingActionModal.id, {
        ...payload,
        is_payment_clear: bookingActionModal.dispute_status,
      })
    }

    if (!bookings.usingMocks)
      await bookings.load()

    closeBookingActionModal()
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
                <VCol cols="6">
                  <BaseButton
                    v-if="bookingStatusLabel(selectedBooking) !== 'Cancelled'"
                    variant="tonal"
                    color="warning"
                    block
                    label="Cancel booking"
                    prepend-icon="bx-calendar-x"
                    @click="askCancel(selectedBooking.id)"
                  />
                  <BaseButton
                    v-else
                    variant="tonal"
                    color="primary"
                    block
                    label="Resolve dispute"
                    prepend-icon="bx-check-circle"
                    @click="askResolveDispute(selectedBooking.id)"
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
                Snapshot for {{ selectedMonthBounds.label }}
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Guest Bookings (Active)
                    </div>
                    <div class="text-h5">
                      {{ stats.guestActive }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--danger pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Cancelled Bookings
                    </div>
                    <div class="text-h5">
                      {{ stats.cancelled }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="d-flex align-center gap-1 text-caption text-medium-emphasis">
                      <span>Cleared Bookings</span>
                      <button
                        type="button"
                        class="cleared-bookings-info-btn"
                        :aria-label="clearedBookingsHint"
                      >
                        <VIcon
                          icon="bx-info-circle"
                          size="16"
                        />
                        <VTooltip
                          activator="parent"
                          location="top"
                          max-width="260"
                          :open-on-hover="false"
                          open-on-click
                        >
                          {{ clearedBookingsHint }}
                        </VTooltip>
                      </button>
                    </div>
                    <div class="text-h5">
                      {{ stats.cleared }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Owner Blocks
                    </div>
                    <div class="text-h5">
                      {{ stats.ownerBlocks }}
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
                    v-if="stats.chartTotal > 0"
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
          <VProgressLinear
            v-if="bookings.loading"
            indeterminate
            class="flex-shrink-0"
          />

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

          <div class="booking-list">
            <button
              v-for="booking in rows"
              :key="booking.id"
              type="button"
              class="booking-list-item"
              :class="{ 'booking-list-item--selected': String(selectedId) === String(booking.id) }"
              @click="selectBooking(booking)"
            >
              <span
                class="booking-list-item__flag"
                :style="{ background: bookingStatusColor(booking) }"
              >
                {{ bookingPlatformLabel(booking) }}
              </span>

              <VChip
                size="x-small"
                :color="bookingStatusColor(booking)"
                label
                class="booking-list-item__status"
              >
                {{ bookingStatusLabel(booking) }}
              </VChip>

              <div class="booking-list-item__content">
                <div class="booking-list-item__property">
                  <VAvatar
                    size="52"
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

                  <div class="booking-list-item__property-text">
                    <div class="booking-list-item__title text-truncate">
                      {{ bookingPropertyTitle(booking) }}
                    </div>
                    <div class="booking-list-item__subtitle text-truncate">
                      {{ bookingLocation(booking) }}
                    </div>
                  </div>
                </div>

                <div class="booking-list-item__guest">
                  <div class="booking-list-item__guest-name text-truncate">
                    {{ booking.guest_name || '—' }}
                  </div>
                  <div class="booking-list-item__code text-truncate">
                    <span class="booking-list-item__code-bar" aria-hidden="true" />
                    {{ booking.booking_code || booking.id }}
                  </div>
                  <div class="booking-list-item__dates">
                    {{ formatDate(booking.reservation_start_date) }}
                    –
                    {{ formatDate(booking.reservation_end_date) }}
                  </div>
                </div>

                <div class="booking-list-item__money">
                  <div class="booking-list-item__money-line">
                    <span class="booking-list-item__money-label">Guest Paid:</span>
                    <span class="booking-list-item__money-value">{{ bookingGuestPaidLabel(booking) }}</span>
                  </div>
                  <div class="booking-list-item__money-line">
                    <span class="booking-list-item__money-label">AC revenue:</span>
                    <span class="booking-list-item__money-value">{{ bookingAcRevenueLabel(booking) }}</span>
                  </div>
                </div>
              </div>
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

    <VDialog
      v-model="bookingActionModal.open"
      max-width="640"
      @after-leave="resetBookingActionModal"
    >
      <VCard v-if="actionModalBooking">
        <VCardTitle>{{ actionModalTitle }}</VCardTitle>
        <VCardText>
          <div class="booking-action-modal__info mb-4">
            <div
              v-for="field in actionModalInfoFields"
              :key="field.label"
              class="booking-action-modal__info-row"
            >
              <div class="text-caption text-medium-emphasis">
                {{ field.label }}
              </div>
              <div class="text-body-2">
                {{ field.value }}
              </div>
            </div>
          </div>

          <VRow>
            <VCol cols="12">
              <BaseSelect
                v-model="bookingActionModal.cancelled_booking"
                label="Cancelled Booking"
                :items="cancelledBookingOptions"
                clearable
              />
            </VCol>
            <VCol cols="12">
              <BaseSelect
                v-model="bookingActionModal.dispute_status"
                label="Dispute Status"
                :items="disputeStatusOptions"
                clearable
              />
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <BaseButton
            variant="tonal"
            label="Close"
            @click="closeBookingActionModal"
          />
          <BaseButton
            :color="actionModalConfirmColor"
            :label="actionModalConfirmLabel"
            :loading="bookings.loading"
            @click="onConfirmBookingAction"
          />
        </VCardActions>
      </VCard>
    </VDialog>
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
  position: relative;
  display: block;
  flex-shrink: 0;
  width: 100%;
  padding: 2.25rem 1rem 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  text-align: left;
  cursor: pointer;
  overflow: hidden;
  transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.booking-list-item:hover {
  background: rgba(104, 101, 255, 0.04);
  border-color: rgba(104, 101, 255, 0.28);
}

.booking-list-item--selected {
  background: rgba(104, 101, 255, 0.1);
  border-color: rgba(104, 101, 255, 0.45);
  box-shadow: 0 0 0 1px rgba(104, 101, 255, 0.12);
}

.booking-list-item__flag {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
  max-width: calc(100% - 7rem);
  padding: 4px 14px 5px 12px;
  border-radius: 12px 0 10px 0;
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  line-height: 1.3;
  text-transform: uppercase;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background: #6c757d;
}

.booking-list-item__status {
  position: absolute;
  top: 8px;
  right: 10px;
  z-index: 1;
}

.booking-list-item__content {
  display: flex;
  align-items: center;
  gap: 1rem 1.25rem;
  min-width: 0;
}

.booking-list-item__property {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1.15 1 0;
  min-width: 0;
}

.booking-list-item__thumb {
  flex-shrink: 0;
}

.booking-list-item__property-text {
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

.booking-list-item__guest {
  flex: 1 1 0;
  min-width: 0;
}

.booking-list-item__guest-name {
  font-size: 0.9375rem;
  font-weight: 600;
  line-height: 1.3;
  color: rgb(var(--v-theme-on-surface));
}

.booking-list-item__code {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 4px;
  font-size: 0.8125rem;
  font-weight: 500;
  line-height: 1.3;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.booking-list-item__code-bar {
  display: inline-block;
  flex-shrink: 0;
  width: 2px;
  height: 0.875rem;
  border-radius: 1px;
  background: rgba(var(--v-theme-on-surface), 0.75);
}

.booking-list-item__dates {
  margin-top: 4px;
  font-size: 0.8125rem;
  line-height: 1.3;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.booking-list-item__money {
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  margin-left: auto;
}

.booking-list-item__money-line {
  display: flex;
  align-items: baseline;
  gap: 4px;
  font-size: 0.8125rem;
  line-height: 1.35;
  white-space: nowrap;
}

.booking-list-item__money-label {
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.booking-list-item__money-value {
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.85);
}

@media (max-width: 700px) {
  .booking-list-item__content {
    flex-wrap: wrap;
  }

  .booking-list-item__property,
  .booking-list-item__guest {
    flex: 1 1 100%;
  }

  .booking-list-item__money {
    width: 100%;
    align-items: flex-start;
    margin-left: 0;
  }
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
  background: rgba(22, 163, 74, 0.08);
}

.entity-stat-tile--danger {
  background: rgba(234, 84, 85, 0.1);
}

.entity-stat-tile--primary {
  background: rgba(104, 101, 255, 0.1);
}

.entity-stat-tile--info {
  background: rgba(104, 101, 255, 0.1);
}

.entity-stat-tile--warning {
  background: rgba(217, 119, 6, 0.12);
}

.cleared-bookings-info-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  background: transparent;
  color: inherit;
  cursor: pointer;
  line-height: 1;
}

.booking-action-modal__info {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem 1rem;
  padding: 0.75rem 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
  background: rgba(75, 70, 92, 0.03);
}

.booking-action-modal__info-row:nth-child(3) {
  grid-column: 1 / -1;
}

@media (max-width: 599px) {
  .booking-action-modal__info {
    grid-template-columns: 1fr;
  }

  .booking-action-modal__info-row:nth-child(3) {
    grid-column: auto;
  }
}
</style>
