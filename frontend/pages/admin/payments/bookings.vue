<script setup>
const payments = usePaymentsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  booking_id: '',
  payment_type_id: '',
  amount: '',
  payment_date: '',
  notes: '',
})

const filters = reactive({
  booking_id: '',
  amount: '',
})

const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = payments.data?.data || payments.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const bookingQuery = filters.booking_id.trim()
  const amountQuery = filters.amount.trim()

  return allRows.value.filter(payment => {
    if (bookingQuery && String(payment.booking_id) !== bookingQuery)
      return false

    if (amountQuery && !String(payment.amount ?? '').includes(amountQuery))
      return false

    return true
  })
})

const stats = computed(() => {
  const list = allRows.value
  const totalAmount = list.reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
  const withNotes = list.filter(payment => Boolean(payment.notes))
  const withType = list.filter(payment => payment.payment_type_id != null)
  const thisMonth = list.filter(payment => {
    if (!payment.payment_date)
      return false

    const date = new Date(payment.payment_date)
    if (Number.isNaN(date.getTime()))
      return false

    const now = new Date()

    return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()
  })

  return {
    total: list.length,
    totalAmount,
    withNotes: withNotes.length,
    withType: withType.length,
    thisMonth: thisMonth.length,
  }
})

const statusChartSeries = computed(() => [stats.value.withType, stats.value.total - stats.value.withType])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Typed', 'Untyped'],
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
  tooltip: { y: { formatter: value => `${value} payments` } },
}))

const activityChartSeries = computed(() => ([{
  name: 'Payments',
  data: [stats.value.thisMonth, stats.value.withNotes, Math.round(stats.value.totalAmount)],
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
    categories: ['This month', 'With notes', 'Amount'],
    labels: { style: { colors: '#a5a3ae', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: { style: { colors: '#a5a3ae' }, formatter: value => Math.round(value) },
    min: 0,
    forceNiceScale: true,
  },
  tooltip: { y: { formatter: value => String(value) } },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter costs & expenses'
  if (panelMode.value === 'create')
    return 'Create payment'

  return 'Payment overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = 'dashboard'

    return
  }

  if (mode === 'create')
    resetForm()

  panelMode.value = mode
}

const clearFilters = () => {
  Object.assign(filters, { booking_id: '', amount: '' })
}

const resetForm = () => {
  Object.assign(form, {
    booking_id: '',
    payment_type_id: '',
    amount: '',
    payment_date: '',
    notes: '',
  })
}

const submit = async () => {
  try {
    await payments.create({
      booking_id: Number(form.booking_id),
      payment_type_id: form.payment_type_id || null,
      amount: Number(form.amount),
      payment_date: form.payment_date || null,
      notes: form.notes || null,
    })
    resetForm()
    panelMode.value = 'dashboard'
    await payments.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

const askDelete = id => {
  confirmDelete.open = true
  confirmDelete.id = id
}

const onDelete = async () => {
  try {
    await payments.remove(confirmDelete.id)
    confirmDelete.open = false
    await payments.load()
  }
  catch {
    // Store exposes errors.
  }
}

onMounted(() => payments.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Costs & Expenses"
      subtitle="Booking payment and expense records"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All payments"
          :loading="payments.loading"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No payments found"
          :empty-description="allRows.length && rows.length === 0 ? 'No payments match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>ID</th>
                <th>Booking</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="payment in rows"
            :key="payment.id"
          >
            <td>{{ payment.id }}</td>
            <td>{{ payment.booking_id }}</td>
            <td>{{ payment.amount }}</td>
            <td>{{ payment.payment_date || '—' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(payment.id)"
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
                  :variant="panelMode === 'create' ? 'flat' : 'tonal'"
                  :color="panelMode === 'create' ? 'primary' : undefined"
                  label="Create"
                  prepend-icon="bx-plus"
                  @click="openPanel('create')"
                />
              </div>
            </template>
          </VCardItem>

          <VCardText>
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of booking payments
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Payments
                    </div>
                    <div class="text-h5">
                      {{ stats.total }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      This month
                    </div>
                    <div class="text-h5">
                      {{ stats.thisMonth }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Total amount
                    </div>
                    <div class="text-h5">
                      {{ stats.totalAmount.toLocaleString(undefined, { maximumFractionDigits: 2 }) }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Payment type coverage
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
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
                    Activity
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
            </div>

            <div v-else-if="panelMode === 'filter'">
              <VForm @submit.prevent>
                <BaseInput
                  v-model="filters.booking_id"
                  label="Booking ID"
                  class="mb-2"
                />
                <BaseInput
                  v-model="filters.amount"
                  label="Amount contains"
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

            <div v-else>
              <AppAlert :errors="payments.errors" />
              <VForm @submit.prevent="submit">
                <BaseInput
                  v-model="form.booking_id"
                  label="Booking ID"
                  type="number"
                  :error="payments.errors.booking_id"
                />
                <BaseInput
                  v-model="form.payment_type_id"
                  label="Payment type ID"
                  type="number"
                  :error="payments.errors.payment_type_id"
                />
                <BaseInput
                  v-model="form.amount"
                  label="Amount"
                  type="number"
                  :error="payments.errors.amount"
                />
                <BaseInput
                  v-model="form.payment_date"
                  label="Payment date"
                  type="date"
                  :error="payments.errors.payment_date"
                />
                <BaseTextarea
                  v-model="form.notes"
                  label="Notes"
                  :error="payments.errors.notes"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="submit"
                    label="Create payment"
                    :loading="payments.loading"
                  />
                  <BaseButton
                    type="button"
                    variant="tonal"
                    label="Cancel"
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
      title="Delete payment?"
      message="This removes the booking payment."
      confirm-label="Delete"
      confirm-color="error"
      :loading="payments.loading"
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

.entity-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.entity-stat-tile--warning {
  background: rgba(255, 159, 67, 0.1);
}
</style>
