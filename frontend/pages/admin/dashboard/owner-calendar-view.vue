<script setup>
import {
  BOOKING_CANCELLED_OPTIONS,
  BOOKING_DISPUTE_STATUS_OPTIONS,
} from '@/constants/bookings'
import {
  BOOKING_STATUS_CANCELLED_CALENDAR,
  BOOKING_STATUS_GUEST,
  BOOKING_STATUS_OWNER_BLOCK,
  COLOR_PRIMARY,
  bookingStatusHex,
} from '@/constants/uiColors'
import {
  LATE_CANCELLATION_LABEL,
  UPDATE_TERMS_LABEL,
  nightsBetween,
} from '@/constants/ownerBlocks'
import {
  MOCK_BOOKINGS,
  bookingLocation,
  bookingPlatformLabel,
  bookingPropertyTitle,
  bookingStatusColor,
  bookingStatusLabel,
  isCancelledBooking,
  isOwnerBlockBooking,
} from '@/mocks/bookings'
import {
  MOCK_PROPERTIES,
} from '@/mocks/properties'
import {
  MOCK_OWNER_BLOCKS,
  ownerBlockExitCleaningLabel,
  ownerBlockPropertyTitle,
} from '@/mocks/ownerBlocks'

definePageMeta({ middleware: 'auth' })

const propertiesStore = usePropertiesStore()
const ownerBlocks = useOwnerBlocksStore()
const bookings = useBookingsStore()

const today = new Date()
const viewYear = ref(today.getFullYear())
const viewMonth = ref(today.getMonth()) // 0-indexed
const propertyId = ref(null)

/** @type {import('vue').Ref<'dashboard' | 'detail'>} */
const panelMode = ref('dashboard')
const selectedId = ref(null)
const panelBodyRef = ref(null)

const confirmBookingDelete = reactive({ open: false, id: null })
const blockDeleteDialog = ref(false)
const deletionConfirmation = ref(false)
const lateCancellationConfirmation = ref(false)
const showLateCancellation = ref(false)
const deleteError = ref('')

/** @type {{ open: boolean, id: string|number|null, cancelled_booking: string|null, dispute_status: string|null }} */
const cancelModal = reactive({
  open: false,
  id: null,
  cancelled_booking: null,
  dispute_status: null,
})

const cancelledBookingOptions = BOOKING_CANCELLED_OPTIONS
const disputeStatusOptions = BOOKING_DISPUTE_STATUS_OPTIONS

const WEEKDAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

const currentYear = today.getFullYear()

const yearFilterItems = Array.from({ length: currentYear - 2014 + 2 }, (_, index) => {
  const year = 2014 + index

  return { title: String(year), value: year }
}).reverse()

const monthFilterItems = monthNames.map((name, index) => ({
  title: `${String(index + 1).padStart(2, '0')} ${name}`,
  value: index + 1,
}))

/** 1-indexed month for the filter select; calendar viewMonth stays 0-indexed. */
const filterMonth = computed({
  get: () => viewMonth.value + 1,
  set: value => {
    const month = Number(value)
    if (Number.isFinite(month) && month >= 1 && month <= 12)
      viewMonth.value = month - 1
  },
})

const CHECKOUT_END_FRAC = 0.42
const CHECKIN_START_FRAC = 0.58
const BAR_HEIGHT = 22
const BAR_GAP = 4
const DAY_HEADER_HEIGHT = 28

const propertyOptions = computed(() => {
  const data = propertiesStore.data?.data || propertiesStore.data || []
  const list = Array.isArray(data) && data.length ? data : MOCK_PROPERTIES

  return list.map(property => ({
    title: property.property_title || `Property #${property.id}`,
    value: property.id,
    street_address: property.street_address || '',
  }))
})

const monthTitle = computed(() => {
  const date = new Date(viewYear.value, viewMonth.value, 1)

  return date.toLocaleString(undefined, { month: 'long', year: 'numeric' })
})

const usingMocks = computed(() => {
  if (propertiesStore.usingMocks || ownerBlocks.usingMocks || bookings.usingMocks)
    return true

  const blockData = ownerBlocks.data?.data || ownerBlocks.data
  const bookingData = bookings.data?.data || bookings.data

  return !(Array.isArray(blockData) && blockData.length)
    || !(Array.isArray(bookingData) && bookingData.length)
})

const parseDate = value => {
  if (!value)
    return null

  const date = new Date(`${String(value).slice(0, 10)}T12:00:00`)

  return Number.isNaN(date.getTime()) ? null : date
}

const formatDate = value => {
  const date = parseDate(value)
  if (!date)
    return '—'

  return date.toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const formatMdY = value => {
  const date = parseDate(value)
  if (!date)
    return ''

  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const year = date.getFullYear()

  return `${month}/${day}/${year}`
}

const formatShortRange = (start, end) => {
  const startLabel = formatMdY(start)
  const endLabel = formatMdY(end)
  if (!startLabel || !endLabel)
    return ''

  return `${startLabel} - ${endLabel}`
}

const eventLabel = (title, start, end) => {
  const range = formatShortRange(start, end)

  return range ? `${title} (${range})` : title
}

const toDateKey = date => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

const eachStayDayKey = (start, end) => {
  const startDate = parseDate(start)
  const endDate = parseDate(end)
  if (!startDate || !endDate || endDate < startDate)
    return []

  const keys = []
  const cursor = new Date(startDate)

  while (cursor <= endDate) {
    keys.push(toDateKey(cursor))
    cursor.setDate(cursor.getDate() + 1)
  }

  return keys
}

/** Overnight nights for a stay: [start, end) calendar days. */
const eachNightKey = (start, end) => {
  const startDate = parseDate(start)
  const endDate = parseDate(end)
  if (!startDate || !endDate || endDate <= startDate)
    return []

  const keys = []
  const cursor = new Date(startDate)

  while (cursor < endDate) {
    keys.push(toDateKey(cursor))
    cursor.setDate(cursor.getDate() + 1)
  }

  return keys
}

const segmentRole = (dateKey, startKey, endKey) => {
  if (dateKey === startKey && dateKey === endKey)
    return 'same-day'
  if (dateKey === startKey)
    return 'checkin'
  if (dateKey === endKey)
    return 'checkout'

  return 'stay'
}

const eventKind = record => {
  if (record?.kind === 'block' || isOwnerBlockBooking(record))
    return 'block'
  if (isCancelledBooking(record))
    return 'cancelled'

  return 'confirmed'
}

const eventColor = kind => {
  if (kind === 'cancelled')
    return BOOKING_STATUS_CANCELLED_CALENDAR

  return bookingStatusHex(kind)
}

const eventTitle = record => {
  if (record?.kind === 'block' || isOwnerBlockBooking(record)) {
    const name = [record.first_name, record.last_name].filter(Boolean).join(' ').trim()

    return name || record.guest_name || 'Owner Block'
  }

  return record.guest_name || bookingPropertyTitle(record) || 'Reservation'
}

const listFromStore = (storeData, fallback) => {
  const data = storeData?.data || storeData
  const list = Array.isArray(data) ? data : []

  return list.length ? list : fallback
}

const calendarEvents = computed(() => {
  const selected = propertyId.value
  if (selected == null)
    return []

  const blockRows = listFromStore(ownerBlocks.data, MOCK_OWNER_BLOCKS)
    .filter(block =>
      !block.deleted_at
      && Number(block.property_id) === Number(selected),
    )
    .map(block => ({
      ...block,
      kind: 'block',
      id: `block-${block.id}`,
      recordId: block.id,
      canManipulate: true,
      restricted_manipulation: 0,
    }))

  const bookingRows = listFromStore(bookings.data, MOCK_BOOKINGS)
    .filter(booking =>
      Number(booking.property_id) === Number(selected)
      && !isOwnerBlockBooking(booking),
    )
    .map(booking => ({
      ...booking,
      kind: eventKind(booking),
      id: `booking-${booking.id}`,
      recordId: booking.id,
      canManipulate: false,
      restricted_manipulation: 0,
    }))

  return [...blockRows, ...bookingRows]
})

const halfDaySlots = (startKey, endKey) => {
  const slots = new Set()

  for (const dateKey of eachStayDayKey(startKey, endKey)) {
    const role = segmentRole(dateKey, startKey, endKey)
    if (role === 'checkin' || role === 'same-day')
      slots.add(`${dateKey}:pm`)
    if (role === 'checkout' || role === 'same-day')
      slots.add(`${dateKey}:am`)
    if (role === 'stay') {
      slots.add(`${dateKey}:am`)
      slots.add(`${dateKey}:pm`)
    }
  }

  return slots
}

const slotsOverlap = (left, right) => {
  for (const slot of left) {
    if (right.has(slot))
      return true
  }

  return false
}

const preparedEvents = computed(() => {
  const prepared = calendarEvents.value.map(event => {
    const start = event.reservation_start_date || event.start_date
    const end = event.reservation_end_date || event.end_date
    const startKey = start ? String(start).slice(0, 10) : null
    const endKey = end ? String(end).slice(0, 10) : null
    const dayKeys = eachStayDayKey(start, end)
    const kind = eventKind(event)
    const title = eventTitle(event)

    return {
      ...event,
      kind,
      title,
      label: eventLabel(title, start, end),
      rangeLabel: formatShortRange(start, end),
      color: eventColor(kind),
      start,
      end,
      startKey,
      endKey,
      dayKeys,
      slots: halfDaySlots(startKey, endKey),
    }
  }).filter(event => event.dayKeys.length)

  const laneSlots = []

  return [...prepared]
    .sort((a, b) => String(a.startKey).localeCompare(String(b.startKey)) || String(a.endKey).localeCompare(String(b.endKey)))
    .map(event => {
      let lane = laneSlots.findIndex(occupied => !slotsOverlap(occupied, event.slots))
      if (lane === -1) {
        lane = laneSlots.length
        laneSlots.push(new Set(event.slots))
      }
      else {
        for (const slot of event.slots)
          laneSlots[lane].add(slot)
      }

      return { ...event, lane }
    })
})

const calendarCells = computed(() => {
  const firstOfMonth = new Date(viewYear.value, viewMonth.value, 1)
  const startOffset = firstOfMonth.getDay()
  const daysInMonth = new Date(viewYear.value, viewMonth.value + 1, 0).getDate()
  const cells = []

  for (let index = 0; index < startOffset; index += 1) {
    const date = new Date(viewYear.value, viewMonth.value, 1 - (startOffset - index))
    cells.push({
      key: `pad-start-${toDateKey(date)}`,
      inMonth: false,
      date,
      day: date.getDate(),
      dateKey: toDateKey(date),
      isToday: false,
      col: index,
    })
  }

  for (let day = 1; day <= daysInMonth; day += 1) {
    const date = new Date(viewYear.value, viewMonth.value, day)
    const dateKey = toDateKey(date)

    cells.push({
      key: dateKey,
      inMonth: true,
      date,
      day,
      dateKey,
      isToday: dateKey === toDateKey(today),
      col: (startOffset + day - 1) % 7,
    })
  }

  while (cells.length % 7 !== 0) {
    const last = cells[cells.length - 1].date
    const date = new Date(last)
    date.setDate(date.getDate() + 1)
    cells.push({
      key: `pad-end-${toDateKey(date)}`,
      inMonth: false,
      date,
      day: date.getDate(),
      dateKey: toDateKey(date),
      isToday: false,
      col: cells.length % 7,
    })
  }

  return cells
})

const weekRows = computed(() => {
  const rows = []

  for (let index = 0; index < calendarCells.value.length; index += 7) {
    const cells = calendarCells.value.slice(index, index + 7)
    const weekStartKey = cells[0].dateKey
    const weekEndKey = cells[6].dateKey
    const bars = []
    let laneCount = 0

    for (const event of preparedEvents.value) {
      if (event.endKey < weekStartKey || event.startKey > weekEndKey)
        continue

      const segStartKey = event.startKey > weekStartKey ? event.startKey : weekStartKey
      const segEndKey = event.endKey < weekEndKey ? event.endKey : weekEndKey
      const startCol = cells.findIndex(cell => cell.dateKey === segStartKey)
      const endCol = cells.findIndex(cell => cell.dateKey === segEndKey)
      if (startCol < 0 || endCol < 0)
        continue

      const startsOnCheckIn = segStartKey === event.startKey
      const endsOnCheckOut = segEndKey === event.endKey
      const startFrac = startsOnCheckIn ? CHECKIN_START_FRAC : 0
      const endFrac = endsOnCheckOut ? CHECKOUT_END_FRAC : 1

      const leftPct = ((startCol + startFrac) / 7) * 100
      const rightPct = ((endCol + endFrac) / 7) * 100
      const widthPct = Math.max(rightPct - leftPct, 1.5)

      laneCount = Math.max(laneCount, event.lane + 1)

      bars.push({
        ...event,
        weekKey: `${weekStartKey}-${event.id}`,
        leftPct,
        widthPct,
        roundStart: startsOnCheckIn,
        roundEnd: endsOnCheckOut,
        top: DAY_HEADER_HEIGHT + (event.lane * (BAR_HEIGHT + BAR_GAP)),
        tooltip: `${event.label} · Check-in 4 PM · Check-out 11 AM`,
      })
    }

    const bodyHeight = Math.max(laneCount, 1) * (BAR_HEIGHT + BAR_GAP) + 8

    rows.push({
      key: weekStartKey,
      cells,
      bars,
      laneCount,
      bodyHeight,
      rowHeight: DAY_HEADER_HEIGHT + bodyHeight,
    })
  }

  return rows
})

const selectedEvent = computed(() => {
  if (selectedId.value == null)
    return null

  return preparedEvents.value.find(event => String(event.id) === String(selectedId.value)) || null
})

const isDetailLayout = computed(() => panelMode.value === 'detail' && Boolean(selectedEvent.value))

const isOwnerBlock = computed(() => selectedEvent.value?.kind === 'block')

const panelTitle = computed(() => {
  if (isDetailLayout.value)
    return isOwnerBlock.value ? 'Owner block' : 'Booking'

  return 'Filter'
})

const selectedNightCount = computed(() => {
  const event = selectedEvent.value
  if (!event)
    return null

  if (event.no_of_nights != null && Number(event.no_of_nights) > 0)
    return Number(event.no_of_nights)

  return nightsBetween(event.start, event.end)
})

const nightsCoverage = computed(() => {
  const year = viewYear.value
  const month = viewMonth.value
  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const monthNightKeys = new Set()

  for (let day = 1; day <= daysInMonth; day += 1)
    monthNightKeys.add(toDateKey(new Date(year, month, day)))

  const guestNights = new Set()
  const blockNights = new Set()

  for (const event of preparedEvents.value) {
    for (const key of eachNightKey(event.start, event.end)) {
      if (!monthNightKeys.has(key))
        continue

      if (event.kind === 'block')
        blockNights.add(key)
      else if (event.kind !== 'cancelled')
        guestNights.add(key)
    }
  }

  let guest = 0
  let block = 0
  let available = 0

  for (const key of monthNightKeys) {
    if (guestNights.has(key))
      guest += 1
    else if (blockNights.has(key))
      block += 1
    else
      available += 1
  }

  return {
    guest,
    block,
    available,
    total: daysInMonth,
  }
})

const nightsDiskItems = computed(() => [
  {
    key: 'guest',
    label: 'Guest bookings',
    color: BOOKING_STATUS_GUEST,
    value: nightsCoverage.value.guest,
  },
  {
    key: 'block',
    label: 'Owner blocks',
    color: BOOKING_STATUS_OWNER_BLOCK,
    value: nightsCoverage.value.block,
  },
  {
    key: 'available',
    label: 'Available nights',
    color: COLOR_PRIMARY.normal,
    value: nightsCoverage.value.available,
  },
])

const selectedStatusLabel = computed(() => {
  const event = selectedEvent.value
  if (!event)
    return ''
  if (event.kind === 'block')
    return 'Owner Block'
  if (event.kind === 'cancelled')
    return 'Cancelled'

  return bookingStatusLabel(event)
})

const selectedStatusColor = computed(() => {
  const event = selectedEvent.value
  if (!event)
    return 'primary'
  if (event.kind === 'cancelled')
    return 'error'
  if (event.kind === 'block')
    return bookingStatusHex(event.kind)

  return bookingStatusColor(event)
})

const canCancelSelected = computed(() => {
  const event = selectedEvent.value
  if (!event || event.kind === 'block')
    return false

  return !isCancelledBooking(event)
})

const resetPanelScroll = () => {
  nextTick(() => {
    const el = panelBodyRef.value?.$el ?? panelBodyRef.value
    if (el && typeof el.scrollTop === 'number')
      el.scrollTop = 0
  })
}

const selectEvent = event => {
  selectedId.value = event.id
  panelMode.value = 'detail'
  resetPanelScroll()
}

const closeDetail = () => {
  selectedId.value = null
  panelMode.value = 'dashboard'
}

const shiftMonth = delta => {
  const next = new Date(viewYear.value, viewMonth.value + delta, 1)
  viewYear.value = next.getFullYear()
  viewMonth.value = next.getMonth()
}

const goToday = () => {
  viewYear.value = today.getFullYear()
  viewMonth.value = today.getMonth()
}

const goView = () => {
  const event = selectedEvent.value
  if (!event)
    return

  if (event.kind === 'block') {
    navigateTo({
      path: '/admin/dashboard/ownerblock/all',
      query: { edit: String(event.recordId) },
    })
  }
  else {
    navigateTo(`/admin/bookings/${event.recordId}`)
  }
}

const askCancel = () => {
  const event = selectedEvent.value
  if (!event || event.kind === 'block')
    return

  cancelModal.id = event.recordId
  cancelModal.cancelled_booking = '1'
  cancelModal.dispute_status = '1'
  cancelModal.open = true
}

const closeCancelModal = () => {
  cancelModal.open = false
}

const resetCancelModal = () => {
  cancelModal.id = null
  cancelModal.cancelled_booking = null
  cancelModal.dispute_status = null
}

const cancelModalBooking = computed(() => {
  if (cancelModal.id == null)
    return null

  return preparedEvents.value.find(event =>
    event.kind !== 'block' && String(event.recordId) === String(cancelModal.id),
  ) || selectedEvent.value
})

const cancelModalInfoFields = computed(() => {
  const booking = cancelModalBooking.value
  if (!booking)
    return []

  return [
    { label: 'Booking Code', value: booking.booking_code || String(booking.recordId) },
    { label: 'Status', value: bookingStatusLabel(booking) },
    { label: 'Property', value: bookingPropertyTitle(booking) },
    { label: 'Guest', value: booking.guest_name || booking.title || '—' },
    { label: 'Platform', value: bookingPlatformLabel(booking) || '—' },
    { label: 'Reservation Start', value: formatDate(booking.start) },
    { label: 'Reservation End', value: formatDate(booking.end) },
  ]
})

const onConfirmCancel = async () => {
  try {
    if (cancelModal.id == null)
      return

    await bookings.cancel(cancelModal.id, {
      cancelled_booking: cancelModal.cancelled_booking,
      dispute_status: cancelModal.dispute_status,
    })

    if (!bookings.usingMocks)
      await bookings.load()

    closeCancelModal()
  }
  catch {
    // Store exposes errors.
  }
}

const askDelete = () => {
  const event = selectedEvent.value
  if (!event)
    return

  if (event.kind === 'block') {
    deletionConfirmation.value = false
    lateCancellationConfirmation.value = false
    deleteError.value = ''
    showLateCancellation.value = Number(event.restricted_manipulation) === 2
    blockDeleteDialog.value = true

    return
  }

  confirmBookingDelete.open = true
  confirmBookingDelete.id = event.recordId
}

const onDeleteBooking = async () => {
  try {
    const deletedId = confirmBookingDelete.id
    await bookings.remove(deletedId)
    confirmBookingDelete.open = false
    if (selectedEvent.value && String(selectedEvent.value.recordId) === String(deletedId))
      closeDetail()
    if (!bookings.usingMocks)
      await bookings.load()
  }
  catch {
    // Store exposes errors.
  }
}

const onDeleteBlock = async () => {
  if (showLateCancellation.value && !lateCancellationConfirmation.value) {
    deleteError.value = 'Please confirm the late cancellation terms in order to proceed with deletion'

    return
  }

  if (!deletionConfirmation.value) {
    deleteError.value = 'Please agree to the terms in order to proceed with deletion'

    return
  }

  deleteError.value = ''
  const event = selectedEvent.value
  if (!event || event.kind !== 'block')
    return

  try {
    await ownerBlocks.remove(event.recordId)
    blockDeleteDialog.value = false
    closeDetail()
    if (!ownerBlocks.usingMocks)
      await ownerBlocks.load()
  }
  catch {
    deleteError.value = ownerBlocks.errors?.general?.[0] || 'Unable to delete owner block.'
  }
}

watch(propertyId, () => {
  if (selectedId.value && !preparedEvents.value.some(event => String(event.id) === String(selectedId.value)))
    closeDetail()
})

onMounted(async () => {
  await Promise.all([
    propertiesStore.load(),
    ownerBlocks.load(),
    bookings.load(),
  ])

  if (propertyId.value != null)
    return

  const preferred = propertyOptions.value.find(option => Number(option.value) === 101)
  propertyId.value = preferred?.value ?? propertyOptions.value[0]?.value ?? null
})
</script>

<template>
  <div class="owner-calendar-page">
    <PageHeader
      class="flex-shrink-0"
      title="Calendar View"
      subtitle="Guest bookings and owner blocks calendar view."
    />

    <VAlert
      v-if="usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4 owner-calendar-page__alert"
    >
      Showing UI preview data until the calendar API is connected.
    </VAlert>

    <AppAlert
      :errors="ownerBlocks.errors"
      class="mb-4"
    />
    <AppAlert
      :errors="bookings.errors"
      class="mb-4"
    />

    <div
      class="owner-calendar-page__stage"
      :class="{ 'owner-calendar-page__stage--detail': isDetailLayout }"
    >
      <!-- Left: filter/legend · Right in detail: reservation details -->
      <div class="owner-calendar-page__col owner-calendar-page__col--side">
        <VCard class="owner-calendar-panel-card">
          <VCardItem class="flex-shrink-0">
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template
              v-if="isDetailLayout"
              #append
            >
              <VBtn
                icon
                variant="flat"
                color="secondary-light"
                size="small"
                aria-label="Close details"
                @click="closeDetail"
              >
                <VIcon
                  icon="bx-x"
                  color="secondary"
                />
              </VBtn>
            </template>
          </VCardItem>

          <VCardText
            ref="panelBodyRef"
            class="owner-calendar-panel-body"
          >
            <Transition
              name="owner-calendar-panel-fade"
              mode="out-in"
            >
              <div
                v-if="isDetailLayout && selectedEvent"
                :key="`detail-${selectedEvent.id}`"
                class="owner-calendar-detail"
              >
                <div class="d-flex align-center gap-3 mb-4">
                  <VAvatar
                    size="56"
                    rounded="lg"
                    :color="selectedStatusColor"
                    variant="tonal"
                  >
                    <VImg
                      v-if="selectedEvent.property?.property_image_url"
                      :src="selectedEvent.property.property_image_url"
                      cover
                    />
                    <VIcon
                      v-else
                      :icon="isOwnerBlock ? 'bx-calendar-x' : 'bx-calendar-check'"
                      size="26"
                    />
                  </VAvatar>
                  <div class="min-w-0">
                    <div class="d-flex align-center flex-wrap gap-2 mb-1">
                      <h2 class="text-h6 mb-0 text-truncate">
                        {{ isOwnerBlock ? ownerBlockPropertyTitle(selectedEvent) : bookingPropertyTitle(selectedEvent) }}
                      </h2>
                      <VChip
                        size="x-small"
                        :color="selectedStatusColor"
                        :variant="selectedEvent.kind === 'cancelled' ? 'tonal' : 'flat'"
                        label
                      >
                        {{ selectedStatusLabel }}
                      </VChip>
                    </div>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                      {{ selectedEvent.street_address || bookingLocation(selectedEvent) || '—' }}
                    </p>
                  </div>
                </div>

                <div class="d-flex flex-column gap-2 mb-5">
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-user"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedEvent.title }}</span>
                  </div>
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-calendar"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>
                      {{ formatDate(selectedEvent.start) }}
                      –
                      {{ formatDate(selectedEvent.end) }}
                      <template v-if="selectedNightCount">
                        ({{ selectedNightCount }} nights)
                      </template>
                    </span>
                  </div>
                  <div
                    v-if="!isOwnerBlock"
                    class="d-flex align-center gap-2 text-body-2"
                  >
                    <VIcon
                      icon="bx-hash"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedEvent.booking_code || selectedEvent.recordId }}</span>
                  </div>
                  <div
                    v-if="!isOwnerBlock"
                    class="d-flex align-center gap-2 text-body-2"
                  >
                    <VIcon
                      icon="bx-globe"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedEvent.platform || '—' }}</span>
                  </div>
                  <div
                    v-if="isOwnerBlock"
                    class="d-flex align-center gap-2 text-body-2"
                  >
                    <VIcon
                      icon="bx-check-circle"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>
                      Post owner stay cleaning:
                      {{ ownerBlockExitCleaningLabel(selectedEvent) }}
                    </span>
                  </div>
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-message-detail"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedEvent.booking_notes || selectedEvent.owner_notes || 'No notes' }}</span>
                  </div>
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-time"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>
                      Check-in 4 PM · Check-out 11 AM
                    </span>
                  </div>
                </div>

                <VRow dense>
                  <VCol :cols="canCancelSelected ? 12 : 6">
                    <BaseButton
                      color="primary-light"
                      variant="flat"
                      block
                      :label="isOwnerBlock ? 'View block' : 'View booking'"
                      prepend-icon="bx-show"
                      @click="goView"
                    />
                  </VCol>
                  <VCol
                    v-if="canCancelSelected"
                    cols="6"
                  >
                    <BaseButton
                      intent="none"
                      color="warning"
                      variant="tonal"
                      block
                      label="Cancel"
                      prepend-icon="bx-calendar-x"
                      @click="askCancel"
                    />
                  </VCol>
                  <VCol cols="6">
                    <BaseButton
                      block
                      label="Delete"
                      prepend-icon="bx-trash"
                      @click="askDelete"
                    />
                  </VCol>
                </VRow>
              </div>

              <div
                v-else
                key="filter"
              >
                <VRow dense>
                  <VCol cols="12">
                    <BaseSelect
                      v-model="propertyId"
                      label="Properties"
                      :items="propertyOptions"
                      :loading="propertiesStore.loading"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="filterMonth"
                      label="Month"
                      :items="monthFilterItems"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    sm="6"
                  >
                    <BaseSelect
                      v-model="viewYear"
                      label="Year"
                      :items="yearFilterItems"
                    />
                  </VCol>
                </VRow>

                <div class="owner-calendar-nights-panel mt-4">
                  <div class="owner-calendar-nights-panel__total text-caption text-medium-emphasis mb-1">
                    {{ nightsCoverage.total }} nights in selected month
                  </div>
                  <DiskPieChart
                    :items="nightsDiskItems"
                    aria-label="Nights covered by booking type"
                    empty-title="No nights to chart"
                    empty-description="Select a property and month to see night coverage."
                  />
                </div>
              </div>
            </Transition>
          </VCardText>
        </VCard>
      </div>

      <!-- Calendar (right by default; slides left when a reservation is selected) -->
      <div class="owner-calendar-page__col owner-calendar-page__col--calendar">
        <VCard class="owner-calendar-main-card">
          <VCardItem class="flex-shrink-0">
            <div class="owner-calendar-toolbar">
              <div class="d-flex flex-wrap align-center gap-2">
                <BaseButton
                  color="primary-light"
                  variant="flat"
                  size="small"
                  label="Today"
                  @click="goToday"
                />
                <VBtn
                  icon
                  variant="text"
                  size="small"
                  aria-label="Previous month"
                  @click="shiftMonth(-1)"
                >
                  <VIcon icon="bx-chevron-left" />
                </VBtn>
                <VBtn
                  icon
                  variant="text"
                  size="small"
                  aria-label="Next month"
                  @click="shiftMonth(1)"
                >
                  <VIcon icon="bx-chevron-right" />
                </VBtn>
              </div>
              <VCardTitle class="owner-calendar-toolbar__title px-0">
                {{ monthTitle }}
              </VCardTitle>
              <div class="text-body-2 text-medium-emphasis">
                {{ calendarEvents.length }}
                {{ calendarEvents.length === 1 ? 'event' : 'events' }}
              </div>
            </div>
          </VCardItem>
          <VDivider />
          <VCardText class="owner-calendar-main-body pt-4">
            <div
              v-if="ownerBlocks.loading || bookings.loading"
              class="text-body-2 text-medium-emphasis py-8 text-center"
            >
              Loading calendar…
            </div>

            <div
              v-else
              class="owner-calendar"
            >
              <div class="owner-calendar__weekdays">
                <div
                  v-for="label in WEEKDAY_LABELS"
                  :key="label"
                  class="owner-calendar__weekday"
                >
                  {{ label }}
                </div>
              </div>

              <div
                v-for="row in weekRows"
                :key="row.key"
                class="owner-calendar__week"
                :style="{ height: `${row.rowHeight}px` }"
              >
                <div class="owner-calendar__days">
                  <div
                    v-for="cell in row.cells"
                    :key="cell.key"
                    class="owner-calendar__day"
                    :class="{
                      'owner-calendar__day--muted': !cell.inMonth,
                      'owner-calendar__day--today': cell.isToday,
                    }"
                  >
                    <span class="owner-calendar__day-num">{{ cell.day }}</span>
                  </div>
                </div>

                <div class="owner-calendar__bars">
                  <button
                    v-for="bar in row.bars"
                    :key="bar.weekKey"
                    type="button"
                    class="owner-calendar__bar"
                    :class="{
                      'owner-calendar__bar--round-start': bar.roundStart,
                      'owner-calendar__bar--round-end': bar.roundEnd,
                      'owner-calendar__bar--cut-start': !bar.roundStart,
                      'owner-calendar__bar--cut-end': !bar.roundEnd,
                      'owner-calendar__bar--soft': bar.kind === 'cancelled',
                      'owner-calendar__bar--selected': String(selectedId) === String(bar.id),
                    }"
                    :style="{
                      left: `${bar.leftPct}%`,
                      width: `${bar.widthPct}%`,
                      top: `${bar.top}px`,
                      backgroundColor: bar.color,
                    }"
                    :title="bar.tooltip"
                    :aria-label="bar.tooltip"
                    @click="selectEvent(bar)"
                  >
                    <span class="owner-calendar__bar-label">{{ bar.label }}</span>
                  </button>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </div>
    </div>

    <!-- Guest booking delete -->
    <ConfirmDialog
      v-model="confirmBookingDelete.open"
      title="Delete booking?"
      message="This removes the booking from the list (UI preview only for now)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="bookings.loading"
      @confirm="onDeleteBooking"
    />

    <!-- Guest booking cancel (same fields as bookings page) -->
    <VDialog
      v-model="cancelModal.open"
      max-width="640"
      @after-leave="resetCancelModal"
    >
      <VCard v-if="cancelModalBooking">
        <VCardTitle>Cancel booking</VCardTitle>
        <VCardText>
          <div class="booking-action-modal__info mb-4">
            <div
              v-for="field in cancelModalInfoFields"
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
                v-model="cancelModal.cancelled_booking"
                label="Cancelled Booking"
                :items="cancelledBookingOptions"
                clearable
              />
            </VCol>
            <VCol cols="12">
              <BaseSelect
                v-model="cancelModal.dispute_status"
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
            @click="closeCancelModal"
          />
          <BaseButton
            label="Cancel booking"
            :loading="bookings.loading"
            @click="onConfirmCancel"
          />
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Owner block delete (terms confirmation) -->
    <VDialog
      v-model="blockDeleteDialog"
      max-width="520"
    >
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon
              icon="bx-error"
              color="warning"
              size="22"
            />
          </template>
          <VCardTitle>Deletion confirmation</VCardTitle>
        </VCardItem>
        <VDivider />
        <VCardText>
          <VAlert
            v-if="deleteError"
            type="error"
            variant="tonal"
            density="compact"
            class="mb-4"
          >
            {{ deleteError }}
          </VAlert>
          <VAlert
            v-if="showLateCancellation"
            type="warning"
            variant="tonal"
            class="mb-4"
          >
            Due to the last minute nature of this update, if you wish to continue, this may incur a cancel of cleaning charge ($100).
          </VAlert>
          <BaseCheckbox
            v-if="showLateCancellation"
            v-model="lateCancellationConfirmation"
            :label="LATE_CANCELLATION_LABEL"
            class="mb-3"
          />
          <BaseCheckbox
            v-model="deletionConfirmation"
            :label="UPDATE_TERMS_LABEL"
          />
          <div class="text-body-2 mt-2">
            <BaseLink
              href="https://www.airconcierge.net/terms-of-use/"
              new-tab
            >
              Terms of use
            </BaseLink>
          </div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <BaseButton
            variant="tonal"
            label="Cancel"
            @click="blockDeleteDialog = false"
          />
          <BaseButton
            color="error"
            label="Delete"
            prepend-icon="bx-trash"
            :loading="ownerBlocks.loading"
            @click="onDeleteBlock"
          />
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.owner-calendar-page {
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.owner-calendar-page__alert {
  flex: 0 0 auto;
}

.owner-calendar-page__stage {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  min-height: 0;
}

@media (min-width: 960px) {
  .owner-calendar-page {
    height: calc(100dvh - 64px - 3rem);
    overflow: hidden;
  }

  .owner-calendar-page__stage {
    position: relative;
    flex: 1 1 0;
    flex-direction: row;
    gap: 0;
    min-height: 0;
    overflow: hidden;
  }

  .owner-calendar-page__col {
    position: absolute;
    top: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    min-height: 0;
    max-height: 100%;
    transition:
      left 0.45s cubic-bezier(0.22, 1, 0.36, 1),
      width 0.45s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: left, width;
  }

  .owner-calendar-panel-card,
  .owner-calendar-main-card {
    flex: 1 1 auto;
    height: 100%;
    min-height: 0;
    max-height: 100%;
  }

  /* Default: filter 35% left, calendar 65% right */
  .owner-calendar-page__col--side {
    width: calc(35% - 0.5rem);
    left: 0;
  }

  .owner-calendar-page__col--calendar {
    width: calc(65% - 0.5rem);
    left: calc(35% + 0.5rem);
  }

  /* Detail: calendar 60% left, details 40% right */
  .owner-calendar-page__stage--detail .owner-calendar-page__col--calendar {
    width: calc(60% - 0.5rem);
    left: 0;
  }

  .owner-calendar-page__stage--detail .owner-calendar-page__col--side {
    width: calc(40% - 0.5rem);
    left: calc(60% + 0.5rem);
  }
}

@media (min-width: 960px) and (prefers-reduced-motion: reduce) {
  .owner-calendar-page__col {
    transition: none;
  }
}

.owner-calendar-panel-card,
.owner-calendar-main-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.owner-calendar-panel-body,
.owner-calendar-main-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
}

/* Extra room so the first outlined label is not clipped by overflow. */
.owner-calendar-panel-body {
  padding-block-start: 1.25rem;
}

.owner-calendar-panel-fade-enter-active,
.owner-calendar-panel-fade-leave-active {
  transition: opacity 0.2s ease;
}

.owner-calendar-panel-fade-enter-from,
.owner-calendar-panel-fade-leave-to {
  opacity: 0;
}

.owner-calendar-nights-panel {
  padding: 0.875rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 12px;
  background:
    linear-gradient(165deg, rgba(104, 101, 255, 0.05), transparent 42%),
    rgb(var(--v-theme-surface));
}

.owner-calendar-nights-panel__total {
  text-align: start;
  white-space: nowrap;
}

.owner-calendar-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem 1rem;
  width: 100%;
}

.owner-calendar-toolbar__title {
  font-size: 1.25rem;
  font-weight: 600;
}

.owner-calendar {
  border: 1px solid rgba(75, 70, 92, 0.14);
  border-radius: 12px;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
}

.owner-calendar__weekdays {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
}

.owner-calendar__days {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  height: 100%;
}

.owner-calendar__weekday {
  padding: 0.65rem 0.5rem;
  border-bottom: 1px solid rgba(75, 70, 92, 0.12);
  color: rgba(75, 70, 92, 0.65);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-align: center;
  text-transform: uppercase;
}

.owner-calendar__week {
  position: relative;
  border-bottom: 1px solid rgba(75, 70, 92, 0.12);
}

.owner-calendar__week:last-child {
  border-bottom: 0;
}

.owner-calendar__day {
  position: relative;
  height: 100%;
  border-right: 1px solid rgba(75, 70, 92, 0.1);
}

.owner-calendar__day:last-child {
  border-right: 0;
}

.owner-calendar__day--muted {
  background: rgba(75, 70, 92, 0.03);
}

.owner-calendar__day--today {
  background: rgba(var(--v-theme-primary), 0.06);
}

.owner-calendar__day-num {
  position: absolute;
  top: 0.35rem;
  right: 0.45rem;
  z-index: 1;
  color: rgba(75, 70, 92, 0.55);
  font-size: 0.75rem;
  font-weight: 600;
  line-height: 1;
}

.owner-calendar__day--today .owner-calendar__day-num {
  color: rgb(var(--v-theme-primary));
}

.owner-calendar__bars {
  position: absolute;
  inset: 0;
  z-index: 2;
  pointer-events: none;
}

.owner-calendar__bar {
  position: absolute;
  display: flex;
  align-items: center;
  height: 22px;
  padding: 0 0.65rem;
  border: 0;
  border-radius: 0;
  color: #fff;
  background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.14) 0%, rgba(0, 0, 0, 0.08) 100%);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.14);
  text-align: left;
  cursor: pointer;
  overflow: hidden;
  pointer-events: auto;
}

.owner-calendar__bar--round-start {
  border-top-left-radius: 999px;
  border-bottom-left-radius: 999px;
}

.owner-calendar__bar--round-end {
  border-top-right-radius: 999px;
  border-bottom-right-radius: 999px;
}

.owner-calendar__bar--cut-start {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  margin-left: -1px;
  padding-left: calc(0.65rem + 1px);
}

.owner-calendar__bar--cut-end {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  margin-right: -1px;
  padding-right: calc(0.65rem + 1px);
}

.owner-calendar__bar--soft {
  color: #22303E;
  background-image: none;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
}

.owner-calendar__bar--selected {
  outline: 2px solid rgba(255, 255, 255, 0.95);
  outline-offset: 1px;
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.2), 0 2px 6px rgba(0, 0, 0, 0.2);
  z-index: 3;
}

.owner-calendar__bar--soft.owner-calendar__bar--selected {
  outline-color: rgba(34, 48, 62, 0.35);
}

.owner-calendar__bar:hover {
  filter: brightness(1.05);
}

.owner-calendar__bar-label {
  min-width: 0;
  width: 100%;
  font-size: 0.6875rem;
  font-weight: 600;
  line-height: 1.1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.booking-action-modal__info {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem 1rem;
  padding: 0.85rem 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 10px;
  background: rgba(75, 70, 92, 0.03);
}

.booking-action-modal__info-row {
  min-width: 0;
}

@media (max-width: 959px) {
  .owner-calendar-page__col--side {
    order: 1;
  }

  .owner-calendar-page__col--calendar {
    order: 2;
  }

  .owner-calendar-page__stage--detail .owner-calendar-page__col--side {
    order: 2;
  }

  .owner-calendar-page__stage--detail .owner-calendar-page__col--calendar {
    order: 1;
  }
}
</style>
