<script setup>
import {
  PAYMENT_STATUS_OPTIONS,
  PAYMENT_TYPE_CATEGORY_OPTIONS,
} from '@/constants/payments'
import { MANAGEMENT_TYPE_OPTIONS } from '@/constants/properties'
import { MOCK_REGIONS } from '@/mocks/regions'
import {
  MOCK_VENDORS,
  paymentBookingLabel,
  paymentMoney,
} from '@/mocks/payments'
import { MOCK_PROPERTIES } from '@/mocks/properties'

const payments = usePaymentsStore()

/** @type {import('vue').Ref<'property' | 'booking'>} */
const activeSegment = ref('property')

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail'>} */
const panelMode = ref('dashboard')

const currentYear = new Date().getFullYear()
const currentMonth = new Date().getMonth() + 1

const defaultFilters = () => ({
  view_deleted: false,
  region_id: null,
  subregion_id: null,
  property_id: null,
  year: currentYear,
  month: currentMonth,
  payment_status: 'Paid',
  price: '',
  start_date: '',
  end_date: '',
  guest_id: null,
  vendor_name: null,
  payment_type_category: null,
  management_type_id: null,
})

const filters = reactive(defaultFilters())
const listSearch = ref('')
const selectedId = ref(null)
const confirmDelete = reactive({ open: false, id: null, kind: null })
const receiptModal = reactive({ open: false, url: null, title: '' })
const panelBodyRef = ref(null)
const exportNotice = ref('')

const monthNames = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
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
  ...MOCK_REGIONS.map(region => ({
    title: region.region_name || region.name,
    value: region.id,
  })),
]

const subregionFilterItems = computed(() => {
  const region = MOCK_REGIONS.find(item => item.id === filters.region_id)
  const subregions = region?.subregions || []

  return [
    { title: 'All', value: null },
    ...subregions.map(subregion => ({
      title: subregion.subregion_name || subregion.name,
      value: subregion.id,
    })),
  ]
})

const propertyFilterItems = computed(() => {
  const rows = MOCK_PROPERTIES.filter(property => (
    (filters.region_id == null || Number(property.region_id) === Number(filters.region_id))
    && (filters.subregion_id == null || Number(property.subregion_id) === Number(filters.subregion_id))
  ))

  return [
    { title: 'Select', value: null },
    ...rows.map(property => ({
      title: property.property_title,
      value: property.id,
    })),
  ]
})

const vendorFilterItems = [
  { title: 'Select', value: null },
  ...MOCK_VENDORS,
]

const categoryFilterItems = PAYMENT_TYPE_CATEGORY_OPTIONS
const statusFilterItems = PAYMENT_STATUS_OPTIONS

const managementTypeFilterItems = [
  { title: 'All types', value: null },
  ...MANAGEMENT_TYPE_OPTIONS,
]

const guestFilterItems = computed(() => {
  const seen = new Map()

  for (const payment of bookingAllRows.value) {
    const name = String(payment.guest_name || '').trim()
    if (!name || seen.has(name))
      continue

    seen.set(name, name)
  }

  return [
    { title: 'Select', value: null },
    ...[...seen.keys()].map(name => ({ title: name, value: name })),
  ]
})

const bookingAllRows = computed(() => {
  const data = payments.data?.data || payments.data || []

  return Array.isArray(data) ? data : []
})

const propertyAllRows = computed(() => {
  const data = payments.propertyPayments?.data || payments.propertyPayments || []

  return Array.isArray(data) ? data : []
})

const parseDate = value => {
  if (!value)
    return null

  const date = new Date(value)

  return Number.isNaN(date.getTime()) ? null : date
}

const formatDate = value => {
  const date = parseDate(value)
  if (!date)
    return '—'

  return date.toLocaleDateString()
}

const matchesSharedFilters = payment => {
  if (!filters.view_deleted && payment.deleted_at)
    return false
  if (filters.view_deleted && !payment.deleted_at)
    return false

  if (filters.region_id != null && Number(payment.region_id) !== Number(filters.region_id))
    return false
  if (filters.subregion_id != null && Number(payment.subregion_id) !== Number(filters.subregion_id))
    return false
  if (filters.property_id != null && Number(payment.property_id) !== Number(filters.property_id))
    return false

  if (Number(filters.year) > 0) {
    const year = Number(payment.year) || parseDate(payment.expense_incurred_date || payment.payment_date)?.getFullYear()
    if (year !== Number(filters.year))
      return false
  }

  if (Number(filters.month) > 0) {
    const month = Number(payment.month) || ((parseDate(payment.expense_incurred_date || payment.payment_date)?.getMonth() ?? -1) + 1)
    if (month !== Number(filters.month))
      return false
  }

  if (filters.payment_status && String(payment.payment_status) !== filters.payment_status)
    return false

  if (filters.price.trim()) {
    const needle = filters.price.trim()
    if (!String(payment.price ?? payment.amount ?? '').includes(needle))
      return false
  }

  const start = parseDate(filters.start_date)
  const end = parseDate(filters.end_date)
  const incurred = parseDate(payment.expense_incurred_date || payment.payment_date)
  if (start && incurred && incurred < start)
    return false
  if (end && incurred && incurred > end)
    return false

  if (filters.vendor_name && payment.vendor_name !== filters.vendor_name)
    return false
  if (filters.payment_type_category && payment.payment_type_category !== filters.payment_type_category)
    return false
  if (filters.management_type_id != null && Number(payment.management_type_id) !== Number(filters.management_type_id))
    return false

  const search = listSearch.value.trim().toLowerCase()
  if (search) {
    const haystack = [
      payment.id,
      payment.property_title,
      payment.vendor_name,
      payment.payment_type_title,
      payment.payment_type_category,
      payment.booking_code,
      payment.guest_name,
      payment.notes,
    ].join(' ').toLowerCase()

    if (!haystack.includes(search))
      return false
  }

  return true
}

const propertyRows = computed(() => propertyAllRows.value.filter(matchesSharedFilters))

const bookingRows = computed(() => bookingAllRows.value.filter(payment => (
  matchesSharedFilters(payment)
  && (!filters.guest_id || payment.guest_name === filters.guest_id)
)))

const listRows = computed(() => {
  const propertyItems = propertyRows.value.map(payment => ({
    ...payment,
    kind: 'property',
    sortDate: payment.expense_incurred_date || payment.payment_date || '',
  }))

  const bookingItems = bookingRows.value.map(payment => ({
    ...payment,
    kind: 'booking',
    sortDate: payment.expense_incurred_date || payment.payment_date || '',
  }))

  return [...propertyItems, ...bookingItems].sort((a, b) => {
    const aTime = Date.parse(a.sortDate) || 0
    const bTime = Date.parse(b.sortDate) || 0

    return bTime - aTime || Number(b.id) - Number(a.id)
  })
})

const selectedPayment = computed(() => {
  if (selectedId.value == null)
    return null

  return listRows.value.find(payment => (
    payment.kind === activeSegment.value
    && String(payment.id) === String(selectedId.value)
  )) || null
})

const kindLabel = kind => (kind === 'property' ? 'Property payment' : 'Booking payment')

const kindColor = kind => (kind === 'property' ? 'primary' : 'success')

const isDetailLayout = computed(() => panelMode.value === 'detail' && Boolean(selectedPayment.value))

const sumField = (rows, key) => rows.reduce((sum, row) => sum + (Number(row[key]) || 0), 0)

const propertyTotals = computed(() => ({
  price: sumField(propertyRows.value, 'initial_price'),
  convenience: sumField(propertyRows.value, 'convenience_fee'),
  administrative: propertyRows.value.reduce((sum, row) => {
    const base = Number(row.initial_price) || 0
    const markup = Number(row.management_markup) || 0

    return sum + (base * markup) / 100
  }, 0),
  total: sumField(propertyRows.value, 'price'),
}))

const bookingTotals = computed(() => ({
  total: sumField(bookingRows.value, 'price'),
}))

const stats = computed(() => {
  const propertyList = propertyRows.value
  const bookingList = bookingRows.value
  const recurring = [...propertyList, ...bookingList].filter(row => row.is_recurring).length

  return {
    propertyCount: propertyList.length,
    bookingCount: bookingList.length,
    propertyTotal: propertyTotals.value.total,
    bookingTotal: bookingTotals.value.total,
    combinedTotal: propertyTotals.value.total + bookingTotals.value.total,
    recurring,
  }
})

const statusChartSeries = computed(() => [stats.value.propertyCount, stats.value.bookingCount])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Property payments', 'Booking payments'],
  colors: ['#696cff', '#28c76f'],
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
          total: {
            show: true,
            label: 'Records',
            fontSize: '13px',
            formatter: () => String(stats.value.propertyCount + stats.value.bookingCount),
          },
        },
      },
    },
  },
  stroke: { width: 0 },
  tooltip: { y: { formatter: value => `${value} payments` } },
}))

const amountChartSeries = computed(() => ([{
  name: 'Total',
  data: [
    Number(stats.value.propertyTotal.toFixed(2)),
    Number(stats.value.bookingTotal.toFixed(2)),
  ],
}]))

const amountChartOptions = computed(() => ({
  chart: { type: 'bar', parentHeightOffset: 0, toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
  colors: ['#696cff', '#28c76f'],
  dataLabels: { enabled: false },
  legend: { show: false },
  grid: {
    strokeDashArray: 6,
    borderColor: 'rgba(75, 70, 92, 0.12)',
    yaxis: { lines: { show: true } },
    xaxis: { lines: { show: false } },
  },
  xaxis: {
    categories: ['Property', 'Booking'],
    labels: { style: { colors: '#a5a3ae', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: { colors: '#a5a3ae' },
      formatter: value => `$${Math.round(value)}`,
    },
    min: 0,
    forceNiceScale: true,
  },
  tooltip: { y: { formatter: value => paymentMoney(value) } },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter costs & expenses'
  if (isDetailLayout.value)
    return activeSegment.value === 'property' ? 'Property payment' : 'Booking payment'

  return 'Costs & expenses overview'
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

const selectPayment = (payment, kind) => {
  activeSegment.value = kind
  selectedId.value = payment.id
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

const goCreate = () => navigateTo('/admin/payments/create')

const goEdit = (payment, kind = activeSegment.value) => {
  navigateTo(`/admin/payments/${kind}/${payment.id}/edit`)
}

const askDelete = (payment, kind = activeSegment.value) => {
  confirmDelete.open = true
  confirmDelete.id = payment.id
  confirmDelete.kind = kind
}

const onDelete = async () => {
  try {
    if (confirmDelete.kind === 'property')
      await payments.removePropertyPayment(confirmDelete.id)
    else
      await payments.remove(confirmDelete.id)

    confirmDelete.open = false
    if (String(selectedId.value) === String(confirmDelete.id))
      closeDetail()

    if (!payments.usingMocks)
      await payments.loadAll()
  }
  catch {
    // Store exposes errors.
  }
}

const openReceipt = payment => {
  if (!payment?.receipt_url)
    return

  receiptModal.open = true
  receiptModal.url = payment.receipt_url
  receiptModal.title = `Receipt #${payment.id}`
}

const isPdfReceipt = computed(() => String(receiptModal.url || '').toLowerCase().includes('.pdf'))

const downloadCsv = (filename, rows, columns) => {
  const escape = value => `"${String(value ?? '').replaceAll('"', '""')}"`
  const header = columns.map(column => escape(column.label)).join(',')
  const body = rows.map(row => columns.map(column => escape(column.value(row))).join(',')).join('\n')
  const blob = new Blob([`${header}\n${body}`], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')

  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)
}

const exportExcel = () => {
  downloadCsv('property-payments.csv', propertyRows.value, [
    { label: 'ID', value: row => row.id },
    { label: 'Vendor', value: row => row.vendor_name },
    { label: 'Property', value: row => row.property_title },
    { label: 'Category', value: row => row.payment_type_category },
    { label: 'Title', value: row => row.payment_type_title },
    { label: 'Expense Date', value: row => row.expense_incurred_date },
    { label: 'Status', value: row => row.payment_status },
    { label: 'Method', value: row => row.property_payment_type },
    { label: 'Purchaser', value: row => row.purchaser_name },
    { label: 'Price', value: row => row.initial_price },
    { label: 'Convenience Fee', value: row => row.convenience_fee },
    { label: 'Administrative Fee %', value: row => row.management_markup },
    { label: 'Total', value: row => row.price },
    { label: 'Notes', value: row => row.notes },
  ])
  downloadCsv('booking-payments.csv', bookingRows.value, [
    { label: 'ID', value: row => row.id },
    { label: 'Vendor', value: row => row.vendor_name },
    { label: 'Property', value: row => row.property_title },
    { label: 'Booking', value: row => paymentBookingLabel(row) },
    { label: 'Expense/Charge', value: row => row.payment_type_title },
    { label: 'Expense Date', value: row => row.expense_incurred_date },
    { label: 'Payment Date', value: row => row.payment_date },
    { label: 'Status', value: row => row.payment_status },
    { label: 'Method', value: row => row.property_payment_type },
    { label: 'Purchaser', value: row => row.purchaser_name },
    { label: 'Price', value: row => row.price },
    { label: 'Notes', value: row => row.notes },
  ])

  exportNotice.value = 'Exported filtered property and booking payments as CSV (Excel-compatible).'
}

const exportPdf = () => {
  exportNotice.value = 'PDF export uses the same filtered set as Excel once the backend export endpoints are wired.'
}

watch(() => filters.region_id, () => {
  filters.subregion_id = null
})

onMounted(() => payments.loadAll())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div class="costs-page">
    <PageHeader
      class="flex-shrink-0"
      title="Costs & Expenses"
      subtitle="Property and booking payment / expense records"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Export Excel"
          prepend-icon="bx-export"
          @click="exportExcel"
        />
        <BaseButton
          variant="tonal"
          label="Export PDF"
          prepend-icon="bx-file"
          @click="exportPdf"
        />
        <BaseButton
          color="primary"
          label="Add New Payment"
          prepend-icon="bx-plus"
          @click="goCreate"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="payments.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-3"
    >
      Showing UI preview data until the payments API is connected.
    </VAlert>

    <VAlert
      type="info"
      variant="tonal"
      density="compact"
      class="mb-3"
    >
      Some expenses related to a property may appear on the booking record under Account Adjustment (Expense),
      depending on when and how the expense was incurred.
    </VAlert>

    <VAlert
      v-if="exportNotice"
      type="success"
      variant="tonal"
      density="compact"
      class="mb-3"
      closable
      @click:close="exportNotice = ''"
    >
      {{ exportNotice }}
    </VAlert>

    <div class="costs-page__stage">
      <div class="costs-page__col costs-page__col--list">
        <VCard class="costs-list-card">
          <VCardItem class="flex-shrink-0">
            <VCardTitle>All costs & expenses</VCardTitle>
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

          <VCardText class="costs-list-card__search flex-shrink-0">
            <BaseInput
              v-model="listSearch"
              placeholder="Search vendor, property, booking, notes..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            v-if="payments.loading"
            indeterminate
            class="flex-shrink-0"
          />

          <div class="costs-list-wrap">
            <EmptyState
              v-if="!payments.loading && listRows.length === 0"
              title="No payments found"
              description="Adjust filters or add a new payment."
            />
            <div
              v-else
              class="costs-item-list"
            >
              <button
                v-for="payment in listRows"
                :key="`${payment.kind}-${payment.id}`"
                type="button"
                class="costs-item"
                :class="{
                  'costs-item--selected': activeSegment === payment.kind && String(selectedId) === String(payment.id),
                  'costs-item--recurring': payment.is_recurring,
                  'costs-item--deleted': Boolean(payment.deleted_at),
                }"
                @click="selectPayment(payment, payment.kind)"
              >
                <div class="costs-item__main">
                  <div class="d-flex align-center flex-wrap gap-2 mb-1">
                    <VChip
                      size="x-small"
                      :color="kindColor(payment.kind)"
                      variant="tonal"
                      label
                    >
                      {{ kindLabel(payment.kind) }}
                    </VChip>
                    <VChip
                      size="x-small"
                      :color="payment.payment_status === 'Paid' ? 'success' : 'warning'"
                      label
                    >
                      {{ payment.payment_status || '—' }}
                    </VChip>
                  </div>
                  <div class="costs-item__title text-truncate">
                    {{ payment.property_title || `Property #${payment.property_id}` }}
                  </div>
                  <div class="costs-item__subtitle text-truncate">
                    {{ payment.vendor_name || 'No vendor' }}
                    ·
                    {{ payment.payment_type_title || payment.payment_type_category || 'Expense' }}
                  </div>
                  <div
                    v-if="payment.kind === 'booking'"
                    class="costs-item__meta text-truncate"
                  >
                    {{ paymentBookingLabel(payment) }}
                  </div>
                  <div class="costs-item__meta text-truncate">
                    <template v-if="payment.kind === 'booking'">
                      Incurred {{ formatDate(payment.expense_incurred_date) }}
                      · Paid {{ formatDate(payment.payment_date) }}
                    </template>
                    <template v-else>
                      {{ formatDate(payment.expense_incurred_date) }}
                      ·
                      {{ payment.purchaser_name || '—' }}
                      ·
                      {{ payment.property_payment_type || '—' }}
                    </template>
                  </div>
                </div>
                <div class="costs-item__aside">
                  <div class="costs-item__amount">
                    {{ paymentMoney(payment.price ?? payment.amount) }}
                  </div>
                  <div
                    class="costs-item__actions"
                    @click.stop
                  >
                    <BaseButton
                      v-if="payment.receipt_url"
                      size="small"
                      variant="text"
                      label="Receipt"
                      @click="openReceipt(payment)"
                    />
                    <BaseButton
                      size="small"
                      variant="text"
                      label="Edit"
                      @click="goEdit(payment, payment.kind)"
                    />
                    <BaseButton
                      size="small"
                      variant="text"
                      color="error"
                      label="Delete"
                      @click="askDelete(payment, payment.kind)"
                    />
                  </div>
                </div>
              </button>
            </div>
          </div>
        </VCard>
      </div>

      <div class="costs-page__col costs-page__col--side">
        <VCard class="costs-panel-card">
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
                  aria-label="Close payment details"
                  @click="closeDetail"
                >
                  <VIcon icon="bx-x" />
                </VBtn>
              </div>
            </template>
          </VCardItem>

          <VCardText
            ref="panelBodyRef"
            class="costs-panel-body"
          >
            <Transition
              name="costs-panel-fade"
              mode="out-in"
            >
              <div
                v-if="isDetailLayout && selectedPayment"
                :key="`detail-${selectedPayment.id}`"
              >
                <div class="d-flex align-center gap-3 mb-4">
                  <VAvatar
                    size="56"
                    rounded="lg"
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon
                      icon="bx-receipt"
                      size="26"
                    />
                  </VAvatar>
                  <div class="min-w-0">
                    <div class="d-flex align-center flex-wrap gap-2 mb-1">
                      <h2 class="text-h6 mb-0 text-truncate">
                        {{ selectedPayment.property_title || '—' }}
                      </h2>
                      <VChip
                        size="x-small"
                        :color="kindColor(activeSegment)"
                        variant="tonal"
                        label
                      >
                        {{ kindLabel(activeSegment) }}
                      </VChip>
                      <VChip
                        size="x-small"
                        :color="selectedPayment.payment_status === 'Paid' ? 'success' : 'warning'"
                        label
                      >
                        {{ selectedPayment.payment_status || '—' }}
                      </VChip>
                      <VChip
                        v-if="selectedPayment.is_recurring"
                        size="x-small"
                        color="info"
                        label
                      >
                        Recurring
                      </VChip>
                    </div>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                      {{ selectedPayment.vendor_name || 'No vendor' }}
                      ·
                      {{ selectedPayment.payment_type_title || selectedPayment.payment_type_category || 'Expense' }}
                    </p>
                  </div>
                </div>

                <div class="d-flex flex-column gap-2 mb-5 text-body-2">
                  <div class="d-flex align-center gap-2">
                    <VIcon
                      icon="bx-hash"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>#{{ selectedPayment.id }}</span>
                  </div>
                  <div
                    v-if="activeSegment === 'booking'"
                    class="d-flex align-center gap-2"
                  >
                    <VIcon
                      icon="bx-calendar-check"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ paymentBookingLabel(selectedPayment) }}</span>
                  </div>
                  <div class="d-flex align-center gap-2">
                    <VIcon
                      icon="bx-calendar"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>
                      Incurred {{ formatDate(selectedPayment.expense_incurred_date) }}
                      <template v-if="selectedPayment.payment_date">
                        · Paid {{ formatDate(selectedPayment.payment_date) }}
                      </template>
                    </span>
                  </div>
                  <div class="d-flex align-center gap-2">
                    <VIcon
                      icon="bx-credit-card"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedPayment.property_payment_type || '—' }}</span>
                  </div>
                  <div class="d-flex align-center gap-2">
                    <VIcon
                      icon="bx-user"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedPayment.purchaser_name || '—' }}</span>
                  </div>
                  <div class="d-flex align-center gap-2">
                    <VIcon
                      icon="bx-dollar"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ paymentMoney(selectedPayment.price ?? selectedPayment.amount) }}</span>
                  </div>
                  <div
                    v-if="selectedPayment.notes"
                    class="d-flex align-start gap-2"
                  >
                    <VIcon
                      icon="bx-note"
                      size="18"
                      class="text-medium-emphasis mt-1"
                    />
                    <span>{{ selectedPayment.notes }}</span>
                  </div>
                  <div
                    v-if="selectedPayment.latest_action"
                    class="d-flex align-start gap-2"
                  >
                    <VIcon
                      icon="bx-history"
                      size="18"
                      class="text-medium-emphasis mt-1"
                    />
                    <span>{{ selectedPayment.latest_action }}</span>
                  </div>
                </div>

                <VRow dense>
                  <VCol
                    v-if="selectedPayment.receipt_url"
                    cols="6"
                  >
                    <BaseButton
                      color="primary"
                      block
                      label="View receipt"
                      prepend-icon="bx-show"
                      @click="openReceipt(selectedPayment)"
                    />
                  </VCol>
                  <VCol cols="6">
                    <BaseButton
                      variant="tonal"
                      block
                      label="Edit"
                      prepend-icon="bx-edit"
                      @click="goEdit(selectedPayment)"
                    />
                  </VCol>
                  <VCol cols="6">
                    <BaseButton
                      variant="tonal"
                      color="error"
                      block
                      label="Delete"
                      prepend-icon="bx-trash"
                      @click="askDelete(selectedPayment)"
                    />
                  </VCol>
                </VRow>
              </div>

              <div
                v-else-if="panelMode === 'filter'"
                key="filter"
              >
                <VForm @submit.prevent="applyFilters">
                  <BaseCheckbox
                    v-model="filters.view_deleted"
                    label="View Deleted Entries"
                    hide-details
                    class="mb-4"
                  />

                  <VRow>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.region_id"
                        label="City / Region"
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
                        v-model="filters.property_id"
                        label="Property"
                        :items="propertyFilterItems"
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
                        v-model="filters.payment_status"
                        label="Status"
                        :items="statusFilterItems"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseInput
                        v-model="filters.price"
                        label="Price"
                        placeholder="0.00"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseInput
                        v-model="filters.start_date"
                        label="From"
                        type="date"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseInput
                        v-model="filters.end_date"
                        label="To"
                        type="date"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.guest_id"
                        label="Guest"
                        :items="guestFilterItems"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.vendor_name"
                        label="Vendor Name"
                        :items="vendorFilterItems"
                      />
                    </VCol>
                    <VCol cols="12">
                      <BaseSelect
                        v-model="filters.payment_type_category"
                        label="Expense/Charge Category"
                        :items="categoryFilterItems"
                      />
                    </VCol>
                  </VRow>

                  <div class="d-flex flex-wrap gap-2 mt-4">
                    <BaseButton
                      type="button"
                      variant="tonal"
                      label="Clear"
                      @click="clearFilters"
                    />
                    <BaseButton
                      type="submit"
                      color="primary"
                      label="Search"
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
                  Snapshot of filtered property and booking expenses
                </p>

                <VRow
                  dense
                  class="mb-2"
                >
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Property payments
                      </div>
                      <div class="text-h5">
                        {{ stats.propertyCount }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Booking payments
                      </div>
                      <div class="text-h5">
                        {{ stats.bookingCount }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Combined total
                      </div>
                      <div class="text-h6">
                        {{ paymentMoney(stats.combinedTotal) }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Recurring
                      </div>
                      <div class="text-h5">
                        {{ stats.recurring }}
                      </div>
                    </div>
                  </VCol>
                </VRow>

                <ClientOnly>
                  <div class="mt-4">
                    <div class="text-subtitle-2 mb-2">
                      Record mix
                    </div>
                    <VueApexCharts
                      v-if="stats.propertyCount + stats.bookingCount > 0"
                      type="donut"
                      height="220"
                      :options="statusChartOptions"
                      :series="statusChartSeries"
                    />
                    <EmptyState
                      v-else-if="!payments.loading"
                      title="No payments yet"
                      description="Counts and charts will appear once payments are loaded."
                    />
                  </div>
                  <div class="mt-6">
                    <div class="text-subtitle-2 mb-2">
                      Amount by type
                    </div>
                    <VueApexCharts
                      v-if="stats.propertyCount + stats.bookingCount > 0"
                      type="bar"
                      height="200"
                      :options="amountChartOptions"
                      :series="amountChartSeries"
                    />
                  </div>
                </ClientOnly>
              </div>
            </Transition>
          </VCardText>
        </VCard>
      </div>
    </div>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete payment?"
      message="This removes the cost / expense record."
      confirm-label="Delete"
      confirm-color="error"
      :loading="payments.loading"
      @confirm="onDelete"
    />

    <VDialog
      v-model="receiptModal.open"
      max-width="720"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>{{ receiptModal.title }}</VCardTitle>
          <template #append>
            <VBtn
              icon
              variant="text"
              @click="receiptModal.open = false"
            >
              <VIcon icon="bx-x" />
            </VBtn>
          </template>
        </VCardItem>
        <VCardText>
          <iframe
            v-if="isPdfReceipt"
            :src="receiptModal.url"
            class="costs-receipt-frame"
            title="Payment receipt"
          />
          <VImg
            v-else
            :src="receiptModal.url"
            max-height="560"
            contain
          />
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.costs-page {
  display: flex;
  flex-direction: column;
  min-height: calc(100vh - 7rem);
}

.costs-page__stage {
  display: grid;
  grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
  gap: 1rem;
  align-items: stretch;
  min-height: 0;
  flex: 1;
  width: 100%;
}

.costs-page__col {
  min-width: 0;
  min-height: 0;
  width: 100%;
}

.costs-page__col--list {
  max-width: none;
}

.costs-list-card,
.costs-panel-card {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.costs-list-card__search {
  padding-block: 0 0.75rem;
}

.costs-panel-body {
  flex: 1;
  overflow: auto;
  max-height: calc(100vh - 14rem);
}

.costs-list-wrap {
  flex: 1 1 auto;
  width: 100%;
  min-height: 0;
  overflow: auto;
  padding: 0 1rem 1rem;
}

.costs-item-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 100%;
}

.costs-item {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  justify-content: space-between;
  width: 100%;
  max-width: none;
  box-sizing: border-box;
  padding: 0.875rem 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
  background: rgb(var(--v-theme-surface));
  text-align: left;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.costs-item:hover {
  border-color: rgba(105, 108, 255, 0.35);
}

.costs-item--selected {
  border-color: rgba(105, 108, 255, 0.55);
  background: rgba(105, 108, 255, 0.08);
}

.costs-item--recurring {
  background: rgba(0, 207, 232, 0.08);
}

.costs-item--selected.costs-item--recurring {
  background: rgba(105, 108, 255, 0.1);
}

.costs-item--deleted {
  opacity: 0.65;
}

.costs-item__main {
  min-width: 0;
  flex: 1 1 auto;
}

.costs-item__title {
  font-size: 0.9375rem;
  font-weight: 600;
  line-height: 1.3;
}

.costs-item__subtitle,
.costs-item__meta {
  margin-top: 2px;
  font-size: 0.8125rem;
  line-height: 1.35;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.costs-item__aside {
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.35rem;
}

.costs-item__amount {
  font-size: 0.9375rem;
  font-weight: 600;
  white-space: nowrap;
}

.costs-item__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.15rem;
}

.costs-receipt-frame {
  width: 100%;
  min-height: 520px;
  border: 0;
}

.costs-panel-fade-enter-active,
.costs-panel-fade-leave-active {
  transition:
    opacity 0.22s ease,
    transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
}

.costs-panel-fade-enter-from {
  opacity: 0;
  transform: translateX(16px);
}

.costs-panel-fade-leave-to {
  opacity: 0;
  transform: translateX(-12px);
}

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

.entity-stat-tile--info {
  background: rgba(0, 207, 232, 0.1);
}

@media (max-width: 959px) {
  .costs-page__stage {
    grid-template-columns: 1fr;
  }

  .costs-panel-body {
    max-height: none;
  }

  .costs-item {
    flex-direction: column;
  }

  .costs-item__aside {
    width: 100%;
    align-items: flex-start;
  }

  .costs-item__actions {
    justify-content: flex-start;
  }
}
</style>
