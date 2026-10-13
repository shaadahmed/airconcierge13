<script setup>
import { formatMoney, moneyNumber } from '@/constants/bookings'
import { ownerStatementService } from '@/services/ownerStatementService'

const statements = useOwnerStatementsStore()

const currentYear = new Date().getFullYear()
const currentMonth = new Date().getMonth() + 1

/** @type {import('vue').Ref<'dashboard' | 'filter'>} */
const panelMode = ref('dashboard')

const defaultFilters = () => ({
  property_id: '',
  date_range: 'this_month',
  month: currentMonth,
  year: currentYear,
})

const filters = reactive(defaultFilters())
const draftFilters = reactive(defaultFilters())

const exportNotice = ref('')
const expandedPanels = ref([])

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

const yearItems = [
  { title: 'All', value: '' },
  ...Array.from({ length: currentYear - 2014 + 2 }, (_, index) => {
    const year = 2014 + index

    return { title: String(year), value: year }
  }).reverse(),
]

const monthItems = [
  { title: 'Select', value: '' },
  ...monthNames.map((name, index) => ({
    title: `${String(index + 1).padStart(2, '0')} ${name}`,
    value: index + 1,
  })),
]

const properties = computed(() => statements.meta?.properties || [])

const propertyItems = computed(() => properties.value.map(property => ({
  title: property.property_title || `Property #${property.id}`,
  value: property.id,
})))

const dateRangeItems = computed(() => {
  const ranges = statements.meta?.date_ranges || [
    'last_month',
    'last_quarter',
    'last_year',
    'last_12_months',
    'this_month',
    'this_quarter',
    'this_year',
    'next_month',
    'next_quarter',
    'next_year',
    'specific_month',
  ]

  return ranges.map(value => ({
    title: formatRangeLabel(value),
    value,
  }))
})

const report = computed(() => statements.report)

const selectedProperty = computed(() => {
  if (!filters.property_id)
    return null

  return properties.value.find(property => String(property.id) === String(filters.property_id)) || null
})

const printedOn = computed(() => new Date().toLocaleDateString(undefined, {
  month: 'short',
  day: 'numeric',
  year: 'numeric',
}))

const periodLabel = computed(() => {
  if (report.value?.start_date && report.value?.end_date)
    return `${report.value.start_date} through ${report.value.end_date}`

  if (filters.date_range === 'specific_month') {
    const monthName = monthNames[(Number(filters.month) || 1) - 1] || ''
    const year = filters.year || 'All years'

    return filters.month ? `${monthName} ${year}` : String(year)
  }

  return formatRangeLabel(filters.date_range)
})

const propertySubtitle = computed(() => {
  const property = selectedProperty.value || report.value?.property
  if (!property)
    return ''

  const title = property.property_title || report.value?.property_title || 'Selected property'
  const address = property.street_address || report.value?.street_address

  return address ? `${title} (${address})` : title
})

const incomeLines = computed(() => filterLines(report.value, [
  { key: 'accomodations', label: 'Accommodations' },
  { key: 'cleaning_fee', label: 'Cleaning fee' },
  { key: 'tot_charged_to_guest', label: 'TOT (charged to guest)' },
  { key: 'daily_utility_fee', label: 'Resort fee' },
  { key: 'security_deposit_deductions', label: 'Security deposit deductions' },
  { key: 'pet_fee', label: 'Pet fee' },
  { key: 'airbnb_cohost_payout', label: 'Damage claim (income)' },
  { key: 'accounting_adjustment_income', label: 'Account adjustment income' },
  { key: 'safely_insurance_fee', label: 'Safely insurance fee' },
]))

const expenseLines = computed(() => filterLines(report.value, [
  { key: 'site_listing_fee', label: 'Site listing fee (Airbnb, etc.)' },
  { key: 'credit_card_transaction_fee', label: 'Credit card transaction fee' },
  { key: 'payment_of_cleaners', label: 'Payment to cleaners' },
  { key: 'booking_payments', label: 'Costs / expenses' },
  { key: 'property_payments', label: 'Property payments' },
  { key: 'concierge_restocking', label: 'Concierge & restocking' },
  { key: 'management_fee', label: 'Management fee' },
  { key: 'maintance', label: 'Maintenance' },
  { key: 'total_paid_by_airconcierge_to_city', label: 'TOT (paid by Air Concierge to city)' },
  { key: 'accounting_adjustment', label: 'Account adjustment expense' },
]))

const totalIncome = computed(() => moneyNumber(report.value?.total_income))
const totalExpenses = computed(() => moneyNumber(report.value?.total_expenses))
const netIncome = computed(() => {
  if (report.value?.net_income != null && report.value?.net_income !== '')
    return moneyNumber(report.value.net_income)

  return totalIncome.value - totalExpenses.value
})

const hasStatementData = computed(() => {
  if (!report.value)
    return false

  return totalExpenses.value !== 0
    || totalIncome.value !== 0
    || moneyNumber(report.value.total_reservations) > 0
    || moneyNumber(report.value.booked_nights) > 0
})

const ownerNotes = computed(() => report.value?.owner_notes || [])

const stats = computed(() => ({
  reservations: report.value?.total_reservations ?? 0,
  bookedNights: report.value?.booked_nights ?? 0,
  blockedNights: report.value?.blocked_nights ?? 0,
  occupancy: report.value?.occupancy_percentage ?? 0,
  abr: report.value?.average_daily_rate ?? 0,
  excluded: report.value?.excluded_linked_bookings_count ?? report.value?.excludedLinkedBookingsCount ?? 0,
  income: totalIncome.value,
  expenses: totalExpenses.value,
  net: netIncome.value,
}))

const occupancyMetrics = computed(() => [
  {
    key: 'reservations',
    label: 'Reservations',
    value: stats.value.reservations,
    tone: 'primary',
    icon: 'bx-calendar-check',
  },
  {
    key: 'nights',
    label: 'Booked nights',
    value: stats.value.bookedNights,
    tone: 'info',
    icon: 'bx-moon',
    note: '1',
  },
  {
    key: 'blocked',
    label: 'Owner block nights',
    value: stats.value.blockedNights,
    tone: 'warning',
    icon: 'bx-calendar-x',
  },
  {
    key: 'occupancy',
    label: 'Occupancy',
    value: `${stats.value.occupancy}%`,
    tone: 'success',
    icon: 'bx-pie-chart-alt-2',
  },
  {
    key: 'excluded',
    label: 'Related bookings not shown',
    value: stats.value.excluded,
    tone: 'muted',
    icon: 'bx-link-alt',
    hint: 'Bookings that span related periods may be counted elsewhere.',
  },
  {
    key: 'abr',
    label: 'Average booking rate',
    value: formatCurrency(stats.value.abr),
    tone: 'primary',
    icon: 'bx-dollar-circle',
  },
])

const statusChartSeries = computed(() => [
  Math.max(stats.value.income, 0),
  Math.max(stats.value.expenses, 0),
])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Income', 'Expenses'],
  colors: ['#16A34A', '#DC3545'],
  legend: { position: 'bottom', fontSize: '13px' },
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
            fontSize: '18px',
            fontWeight: 600,
            formatter: value => formatCurrency(value),
          },
          total: {
            show: true,
            label: 'Net',
            fontSize: '13px',
            formatter: () => formatCurrency(stats.value.net),
          },
        },
      },
    },
  },
  stroke: { width: 0 },
  tooltip: { y: { formatter: value => formatCurrency(value) } },
}))

const nightsChartSeries = computed(() => ([{
  name: 'Nights',
  data: [stats.value.bookedNights, stats.value.blockedNights],
}]))

const nightsChartOptions = computed(() => ({
  chart: { type: 'bar', parentHeightOffset: 0, toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
  colors: ['#6865FF', '#D97706'],
  dataLabels: { enabled: false },
  legend: { show: false },
  grid: {
    strokeDashArray: 6,
    borderColor: 'rgba(75, 70, 92, 0.12)',
    yaxis: { lines: { show: true } },
    xaxis: { lines: { show: false } },
  },
  xaxis: {
    categories: ['Booked', 'Owner block'],
    labels: { style: { colors: '#a5a3ae', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: { style: { colors: '#a5a3ae' }, formatter: value => Math.round(value) },
    forceNiceScale: true,
  },
  tooltip: { y: { formatter: value => `${value} nights` } },
}))

const bookingsHref = computed(() => {
  const params = new URLSearchParams()

  if (filters.property_id)
    params.set('property_id', String(filters.property_id))

  const query = params.toString()

  return query ? `/admin/bookings?${query}` : '/admin/bookings'
})

function formatRangeLabel(value) {
  if (value === 'specific_month')
    return 'Specific month / year'

  return String(value || '')
    .split('_')
    .filter(Boolean)
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

function formatCurrency(value) {
  if (value === '' || value == null)
    return '$0.00'

  const raw = String(value).trim()
  if (raw.startsWith('$'))
    return raw

  return `$${formatMoney(value)}`
}

function filterLines(source, definitions) {
  if (!source)
    return []

  return definitions
    .map(definition => ({
      ...definition,
      amount: moneyNumber(source[definition.key]),
    }))
    .filter(line => line.amount !== 0)
}

const openFilter = () => {
  panelMode.value = panelMode.value === 'filter' ? 'dashboard' : 'filter'
  if (panelMode.value === 'filter')
    Object.assign(draftFilters, filters)
}

const clearFilters = () => {
  const next = defaultFilters()
  if (properties.value[0])
    next.property_id = properties.value[0].id

  Object.assign(draftFilters, next)
}

const runReport = async () => {
  if (!filters.property_id)
    return

  try {
    await statements.loadReport({
      property_id: filters.property_id,
      date_range: filters.date_range,
      month: filters.date_range === 'specific_month' ? filters.month : '',
      year: filters.date_range === 'specific_month' ? filters.year : '',
    })
  }
  catch {
    // Store exposes errors.
  }
}

const applyFilters = async () => {
  if (draftFilters.date_range === 'specific_month') {
    if (!draftFilters.year)
      draftFilters.year = currentYear
    if (!draftFilters.month)
      draftFilters.month = currentMonth
  }

  Object.assign(filters, {
    property_id: draftFilters.property_id,
    date_range: draftFilters.date_range,
    month: draftFilters.date_range === 'specific_month' ? draftFilters.month : '',
    year: draftFilters.date_range === 'specific_month' ? draftFilters.year : '',
  })

  await runReport()
  panelMode.value = 'dashboard'
}

const exportCsv = () => {
  if (statements.usingMocks) {
    exportNotice.value = 'CSV export will download once the owner statements API is connected. Preview data is shown below.'

    return
  }

  window.location.href = ownerStatementService.exportUrl({
    property_id: filters.property_id,
    date_range: filters.date_range,
    month: filters.date_range === 'specific_month' ? filters.month : '',
    year: filters.date_range === 'specific_month' ? filters.year : '',
  })
}

const printStatement = () => {
  window.print()
}

watch(
  () => draftFilters.date_range,
  value => {
    if (value !== 'specific_month')
      return

    if (!draftFilters.year)
      draftFilters.year = currentYear
    if (!draftFilters.month)
      draftFilters.month = currentMonth
  },
)

onMounted(async () => {
  await statements.loadMeta()

  if (properties.value[0] && !filters.property_id) {
    filters.property_id = properties.value[0].id
    draftFilters.property_id = properties.value[0].id
  }

  if (filters.property_id)
    await runReport()
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div class="owner-statements-page">
    <PageHeader
      title="P&L Owner Statements"
      subtitle="Profit and loss for a property and date range"
    >
      <template #actions>
        <BaseButton
          class="d-print-none"
          size="small"
          :variant="panelMode === 'filter' ? 'flat' : 'tonal'"
          :color="panelMode === 'filter' ? 'primary' : undefined"
          label="Filter"
          prepend-icon="bx-filter-alt"
          @click="openFilter"
        />
        <BaseButton
          class="d-print-none"
          label="Export"
          variant="tonal"
          prepend-icon="bx-export"
          :disabled="!report || statements.loading"
          @click="exportCsv"
        />
        <BaseButton
          class="d-print-none"
          label="Print"
          variant="tonal"
          prepend-icon="bx-printer"
          :disabled="!report"
          @click="printStatement"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="statements.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4 d-print-none"
    >
      Showing UI preview data until the owner statements API is connected.
    </VAlert>

    <VAlert
      v-if="exportNotice"
      type="success"
      variant="tonal"
      density="compact"
      closable
      class="mb-4 d-print-none"
      @click:close="exportNotice = ''"
    >
      {{ exportNotice }}
    </VAlert>

    <VCard
      v-if="panelMode === 'filter'"
      class="mb-6 d-print-none"
    >
      <VCardItem>
        <VCardTitle>Filter statement</VCardTitle>
      </VCardItem>
      <VCardText>
        <VForm @submit.prevent="applyFilters">
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <BaseSelect
                v-model="draftFilters.property_id"
                label="Property"
                :items="propertyItems"
                :error="statements.errors.property_id"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <BaseSelect
                v-model="draftFilters.date_range"
                label="Date options"
                :items="dateRangeItems"
                :error="statements.errors.date_range"
              />
            </VCol>
            <VCol
              v-if="draftFilters.date_range === 'specific_month'"
              cols="12"
              sm="6"
              md="2"
            >
              <BaseSelect
                v-model="draftFilters.year"
                label="Year"
                :items="yearItems"
                :error="statements.errors.year"
              />
            </VCol>
            <VCol
              v-if="draftFilters.date_range === 'specific_month' && draftFilters.year"
              cols="12"
              sm="6"
              md="2"
            >
              <BaseSelect
                v-model="draftFilters.month"
                label="Month"
                :items="monthItems"
                :error="statements.errors.month"
              />
            </VCol>
          </VRow>

          <div class="d-flex flex-wrap gap-2 mt-2">
            <BaseButton
              type="button"
              variant="tonal"
              label="Clear filters"
              @click="clearFilters"
            />
            <BaseButton
              type="submit"
              color="primary"
              label="Apply filters"
              :loading="statements.loading"
            />
            <BaseButton
              type="button"
              variant="text"
              label="Close"
              @click="panelMode = 'dashboard'"
            />
            <BaseButton
              :href="bookingsHref"
              target="_blank"
              rel="noopener"
              variant="text"
              label="View bookings in this range"
              prepend-icon="bx-search-alt"
            />
          </div>
        </VForm>
      </VCardText>
    </VCard>

    <AppAlert
      :errors="statements.errors"
      class="mb-4"
    />

    <VProgressLinear
      v-if="statements.loading"
      indeterminate
      class="mb-4"
    />

    <EmptyState
      v-if="!report && !statements.loading"
      title="No statement loaded"
      description="Use Filter to choose a property and date range."
    />

    <EmptyState
      v-else-if="report && !hasStatementData && !statements.loading"
      title="No booking found for selected search filters"
      description="Try another property or date range."
    />

    <template v-else-if="hasStatementData">
      <div class="d-flex flex-wrap align-center justify-space-between gap-3 mb-4">
        <div>
          <div class="text-h6 mb-1">
            Profit and Loss Standard
          </div>
          <div class="text-body-2 text-medium-emphasis">
            {{ propertySubtitle }} · {{ periodLabel }}
          </div>
        </div>
        <div class="text-body-2 text-medium-emphasis">
          {{ printedOn }}
        </div>
      </div>

      <VRow>
        <VCol
          cols="12"
          md="7"
          lg="8"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>Statement overview</VCardTitle>
            </VCardItem>

            <VCardText>
              <VRow dense class="mb-4">
                <VCol
                  v-for="metric in occupancyMetrics"
                  :key="metric.key"
                  cols="12"
                  sm="6"
                  md="4"
                >
                  <div
                    class="entity-stat-tile pa-3 rounded h-100"
                    :class="`entity-stat-tile--${metric.tone}`"
                  >
                    <div class="entity-stat-tile__content">
                      <div class="text-caption text-medium-emphasis d-flex align-center gap-1">
                        <span>{{ metric.label }}</span>
                        <sup
                          v-if="metric.note"
                          class="text-primary"
                        >[{{ metric.note }}]</sup>
                        <VTooltip
                          v-if="metric.hint"
                          location="top"
                        >
                          <template #activator="{ props: tipProps }">
                            <VIcon
                              v-bind="tipProps"
                              icon="bx-help-circle"
                              size="16"
                              class="text-primary"
                            />
                          </template>
                          <span>{{ metric.hint }}</span>
                        </VTooltip>
                      </div>
                      <div class="text-h5 mt-1">
                        {{ metric.value }}
                      </div>
                    </div>
                    <VIcon
                      :icon="metric.icon"
                      size="42"
                      class="entity-stat-tile__watermark"
                      aria-hidden="true"
                    />
                  </div>
                </VCol>
              </VRow>

              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <div class="text-subtitle-2 mb-2">
                    Income vs expenses
                  </div>
                  <ClientOnly>
                    <VueApexCharts
                      type="donut"
                      height="240"
                      :options="statusChartOptions"
                      :series="statusChartSeries"
                    />
                  </ClientOnly>
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <div class="text-subtitle-2 mb-2">
                    Nights mix
                  </div>
                  <ClientOnly>
                    <VueApexCharts
                      type="bar"
                      height="240"
                      :options="nightsChartOptions"
                      :series="nightsChartSeries"
                    />
                  </ClientOnly>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>

        <VCol
          cols="12"
          md="5"
          lg="4"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>Income & expenses</VCardTitle>
            </VCardItem>

            <VCardText>
              <VExpansionPanels
                v-model="expandedPanels"
                multiple
                variant="accordion"
                class="statement-accordions"
              >
                <VExpansionPanel value="income">
                  <VExpansionPanelTitle>
                    <div class="statement-accordion-title">
                      <span>Total Income</span>
                      <span class="text-success font-weight-bold">
                        {{ formatCurrency(totalIncome) }}
                      </span>
                    </div>
                  </VExpansionPanelTitle>
                  <VExpansionPanelText>
                    <div
                      v-if="incomeLines.length === 0"
                      class="text-body-2 text-medium-emphasis"
                    >
                      No income line items for this period.
                    </div>
                    <div
                      v-for="line in incomeLines"
                      :key="line.key"
                      class="statement-line d-flex align-center justify-space-between gap-3 py-2"
                    >
                      <span class="text-body-2">{{ line.label }}</span>
                      <span class="text-body-2 font-weight-medium">
                        {{ formatCurrency(line.amount) }}
                      </span>
                    </div>
                  </VExpansionPanelText>
                </VExpansionPanel>

                <VExpansionPanel value="expense">
                  <VExpansionPanelTitle>
                    <div class="statement-accordion-title">
                      <span>Total Expense</span>
                      <span class="text-error font-weight-bold">
                        {{ formatCurrency(totalExpenses) }}
                      </span>
                    </div>
                  </VExpansionPanelTitle>
                  <VExpansionPanelText>
                    <div
                      v-if="expenseLines.length === 0"
                      class="text-body-2 text-medium-emphasis"
                    >
                      No expense line items for this period.
                    </div>
                    <div
                      v-for="line in expenseLines"
                      :key="line.key"
                      class="statement-line d-flex align-center justify-space-between gap-3 py-2"
                    >
                      <span class="text-body-2">
                        {{ line.label }}
                        <sup
                          v-if="line.key === 'booking_payments'"
                          class="text-primary"
                        >[2]</sup>
                      </span>
                      <span class="text-body-2 font-weight-medium">
                        {{ formatCurrency(line.amount) }}
                      </span>
                    </div>
                  </VExpansionPanelText>
                </VExpansionPanel>
              </VExpansionPanels>

              <div class="entity-stat-tile entity-stat-tile--success pa-4 rounded mt-4">
                <div class="d-flex flex-wrap align-center justify-space-between gap-3">
                  <div>
                    <div class="text-caption text-medium-emphasis">
                      {{ report.cohost_property_flag == '1' || report.cohost_property_flag === 1 ? '*' : '' }}Net income
                    </div>
                    <div
                      class="text-h4 mb-0"
                      :class="netIncome >= 0 ? 'text-success' : 'text-error'"
                    >
                      {{ formatCurrency(netIncome) }}
                    </div>
                  </div>
                  <VIcon
                    icon="bx-trending-up"
                    size="48"
                    class="entity-stat-tile__watermark entity-stat-tile__watermark--inline"
                    aria-hidden="true"
                  />
                </div>
              </div>

              <div
                v-if="moneyNumber(report.total_elect_to_remit)"
                class="statement-remit rounded pa-3 mt-4"
              >
                <div class="d-flex align-center justify-space-between gap-3">
                  <span class="text-body-2">TOT should you elect to remit</span>
                  <span class="text-body-2 font-weight-medium">
                    {{ formatCurrency(report.total_elect_to_remit) }}
                  </span>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VCard class="mt-6">
        <VCardItem>
          <VCardTitle>Owner notes</VCardTitle>
          <template #append>
            <span class="text-body-2 text-medium-emphasis">
              {{ ownerNotes.length }} note{{ ownerNotes.length === 1 ? '' : 's' }}
            </span>
          </template>
        </VCardItem>

        <VCardText>
          <EmptyState
            v-if="ownerNotes.length === 0"
            title="No owner notes"
            description="Notes added against bookings in this period will appear here."
          />

          <div
            v-else
            class="owner-notes-list"
          >
            <div
              v-for="(note, index) in ownerNotes"
              :key="index"
              class="owner-note-row"
            >
              <div class="owner-note-meta">
                <a
                  v-if="note.url"
                  :href="note.url"
                  target="_blank"
                  rel="noopener"
                  class="text-decoration-none font-weight-medium"
                >
                  {{ note.guest || 'Guest' }}
                </a>
                <div
                  v-else
                  class="font-weight-medium"
                >
                  {{ note.guest || 'Guest' }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ note.start }} – {{ note.end }}
                </div>
              </div>
              <div class="text-body-2 flex-grow-1">
                {{ note.comment }}
              </div>
            </div>
          </div>
        </VCardText>
      </VCard>

      <VAlert
        type="info"
        variant="tonal"
        density="compact"
        class="mt-4"
      >
        <p class="text-body-2 mb-1">
          <span class="text-primary">[1]</span>
          Reservation nights can exceed days in a month because of how stays are counted versus this month view.
        </p>
        <p class="text-body-2 mb-0">
          <span class="text-primary">[2]</span>
          Costs include maintenance, supplies, and extra duties — details are in Payments.
        </p>
      </VAlert>

      <VAlert
        v-if="report.schema_gaps?.length"
        type="info"
        variant="tonal"
        density="compact"
        class="mt-4 d-print-none"
      >
        Full legacy line items not yet on the L13 bookings schema:
        {{ report.schema_gaps.join(', ') }}.
      </VAlert>
    </template>
  </div>
</template>

<style scoped>
.statement-accordions :deep(.v-expansion-panel-title) {
  padding-inline: 1rem;
  min-height: 3.5rem;
}

.statement-accordion-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 1rem;
  padding-inline-end: 0.5rem;
  font-size: 1rem;
}

.statement-line + .statement-line {
  border-top: 1px solid rgba(75, 70, 92, 0.08);
}

.statement-remit {
  border: 1px solid rgba(75, 70, 92, 0.1);
  background: rgba(75, 70, 92, 0.03);
}

.entity-stat-tile {
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(75, 70, 92, 0.08);
  background: rgba(75, 70, 92, 0.03);
}

.entity-stat-tile__content {
  position: relative;
  z-index: 1;
  padding-right: 1.75rem;
}

.entity-stat-tile__watermark {
  position: absolute;
  right: -0.15rem;
  bottom: -0.35rem;
  opacity: 0.14;
  pointer-events: none;
  color: rgba(75, 70, 92, 0.9);
}

.entity-stat-tile__watermark--inline {
  position: static;
  opacity: 0.22;
}

.entity-stat-tile--success {
  background: rgba(22, 163, 74, 0.08);
}

.entity-stat-tile--success .entity-stat-tile__watermark {
  color: rgb(22, 163, 74);
  opacity: 0.22;
}

.entity-stat-tile--primary {
  background: rgba(104, 101, 255, 0.1);
}

.entity-stat-tile--primary .entity-stat-tile__watermark {
  color: rgb(105, 108, 255);
  opacity: 0.22;
}

.entity-stat-tile--info {
  background: rgba(104, 101, 255, 0.1);
}

.entity-stat-tile--info .entity-stat-tile__watermark {
  color: rgb(0, 186, 209);
  opacity: 0.22;
}

.entity-stat-tile--warning {
  background: rgba(217, 119, 6, 0.1);
}

.entity-stat-tile--warning .entity-stat-tile__watermark {
  color: rgb(217, 119, 6);
  opacity: 0.24;
}

.entity-stat-tile--muted {
  background: rgba(75, 70, 92, 0.04);
}

.owner-notes-list {
  display: flex;
  flex-direction: column;
}

.owner-note-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 0.875rem 0;
}

.owner-note-row + .owner-note-row {
  border-top: 1px solid rgba(75, 70, 92, 0.1);
}

.owner-note-meta {
  min-width: 10rem;
}

@media print {
  .d-print-none {
    display: none !important;
  }
}
</style>
