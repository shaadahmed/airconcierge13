<script setup>
const guests = useGuestsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  guest_name: '',
  phone: '',
  email: '',
  city: '',
  state: '',
  country: '',
  corporate_travel_booking_agent: null,
  corporate_travel_booking_agent_name_email: '',
  corporate_travel_booking_agent_first_name: '',
  corporate_travel_booking_agent_last_name: '',
  corporate_business_name: '',
  notes: '',
})

const corporateAgentItems = [
  { title: 'Select', value: null },
  { title: 'Yes', value: '1' },
  { title: 'No', value: '0' },
]

const filters = reactive({
  name: '',
  email: '',
  city: '',
  country: '',
  blacklisted: null,
})

const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = guests.data?.data || guests.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()
  const emailQuery = filters.email.trim().toLowerCase()
  const cityQuery = filters.city.trim().toLowerCase()
  const countryQuery = filters.country === '' || filters.country === null
    ? null
    : String(filters.country)

  return allRows.value.filter(guest => {
    if (nameQuery) {
      const name = String(guest.guest_name || `${guest.first_name || ''} ${guest.last_name || ''}`).toLowerCase()
      if (!name.includes(nameQuery))
        return false
    }

    if (emailQuery) {
      const email = String(guest.email || '').toLowerCase()
      if (!email.includes(emailQuery))
        return false
    }

    if (cityQuery) {
      const city = String(guest.city || '').toLowerCase()
      if (!city.includes(cityQuery))
        return false
    }

    if (countryQuery !== null && String(guest.country || '') !== countryQuery)
      return false

    if (filters.blacklisted !== null && filters.blacklisted !== undefined && filters.blacklisted !== '') {
      const isBlacklisted = Boolean(guest.blacklisted)
      if (filters.blacklisted === 'yes' && !isBlacklisted)
        return false
      if (filters.blacklisted === 'no' && isBlacklisted)
        return false
    }

    return true
  })
})

const countryOptions = computed(() => {
  const map = new Map()

  for (const guest of allRows.value) {
    const country = guest.country
    if (!country)
      continue

    if (!map.has(country))
      map.set(country, { title: country, value: country })
  }

  return [...map.values()].sort((a, b) => String(a.title).localeCompare(String(b.title)))
})

const blacklistFilterItems = [
  { title: 'All guests', value: null },
  { title: 'Not blacklisted', value: 'no' },
  { title: 'Blacklisted', value: 'yes' },
]

const displayName = guest => {
  if (guest.guest_name)
    return guest.guest_name

  const combined = [guest.first_name, guest.last_name].filter(Boolean).join(' ')

  return combined || '—'
}

const stats = computed(() => {
  const list = allRows.value
  const blacklisted = list.filter(guest => Boolean(guest.blacklisted))
  const clear = list.filter(guest => !guest.blacklisted)
  const withEmail = list.filter(guest => Boolean(guest.email))
  const withPhone = list.filter(guest => Boolean(guest.phone))
  const incompleteContact = list.filter(guest => !guest.email || !guest.phone)

  // UI preview until booking metrics are available from the API.
  // Deterministic split so charts stay stable across reloads.
  const withBookings = clear.filter(guest => Number(guest.id) % 3 !== 0)

  return {
    total: list.length,
    clear: clear.length,
    blacklisted: blacklisted.length,
    withEmail: withEmail.length,
    withPhone: withPhone.length,
    withBookings: withBookings.length,
    incompleteContact: incompleteContact.length,
  }
})

const statusChartSeries = computed(() => [stats.value.clear, stats.value.blacklisted])

const statusChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  labels: ['Clear', 'Blacklisted'],
  colors: ['#28c76f', '#ea5455'],
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
    y: { formatter: value => `${value} guests` },
  },
}))

const activityChartSeries = computed(() => ([
  {
    name: 'Guests',
    data: [
      stats.value.withEmail,
      stats.value.withPhone,
      stats.value.incompleteContact,
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
    categories: ['Email', 'Phone', 'Incomplete'],
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
    y: { formatter: value => `${value} guests` },
  },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter guests'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit guest' : 'Create guest'

  return 'Guest overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = 'dashboard'

    return
  }

  if (mode === 'create' && panelMode.value !== 'create')
    resetForm()

  panelMode.value = mode
}

const clearFilters = () => {
  Object.assign(filters, {
    name: '',
    email: '',
    city: '',
    country: '',
    blacklisted: null,
  })
}

onMounted(() => guests.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    guest_name: '',
    phone: '',
    email: '',
    city: '',
    state: '',
    country: '',
    corporate_travel_booking_agent: null,
    corporate_travel_booking_agent_name_email: '',
    corporate_travel_booking_agent_first_name: '',
    corporate_travel_booking_agent_last_name: '',
    corporate_business_name: '',
    notes: '',
  })
}

const edit = guest => {
  editingId.value = guest.id
  Object.assign(form, {
    guest_name: guest.guest_name || '',
    phone: guest.phone || '',
    email: guest.email || '',
    city: guest.city || '',
    state: guest.state || '',
    country: guest.country || '',
    corporate_travel_booking_agent: guest.corporate_travel_booking_agent ?? null,
    corporate_travel_booking_agent_name_email: guest.corporate_travel_booking_agent_name_email || '',
    corporate_travel_booking_agent_first_name: guest.corporate_travel_booking_agent_first_name || '',
    corporate_travel_booking_agent_last_name: guest.corporate_travel_booking_agent_last_name || '',
    corporate_business_name: guest.corporate_business_name || '',
    notes: guest.notes || '',
  })
  panelMode.value = 'create'
}

const cancelCreate = () => {
  resetForm()
  panelMode.value = 'dashboard'
}

const submit = async () => {
  try {
    const payload = {
      guest_name: form.guest_name,
      phone: form.phone || null,
      email: form.email || null,
      city: form.city || null,
      state: form.state || null,
      country: form.country || null,
      corporate_travel_booking_agent: form.corporate_travel_booking_agent,
      corporate_travel_booking_agent_name_email: form.corporate_travel_booking_agent_name_email || null,
      corporate_travel_booking_agent_first_name: form.corporate_travel_booking_agent_first_name || null,
      corporate_travel_booking_agent_last_name: form.corporate_travel_booking_agent_last_name || null,
      corporate_business_name: form.corporate_business_name || null,
      notes: form.notes || null,
    }

    if (editingId.value)
      await guests.update(editingId.value, payload)
    else
      await guests.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
    await guests.load()
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
    await guests.remove(confirmDelete.id)
    confirmDelete.open = false
    await guests.load()
  }
  catch {
    // Store exposes errors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Guests"
      subtitle="Manage guest records"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All guests"
          :loading="guests.loading"
          :empty="rows.length === 0"
          :empty-colspan="6"
          empty-title="No guests found"
          :empty-description="allRows.length && rows.length === 0 ? 'No guests match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Guest name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>Blacklisted</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="guest in rows"
            :key="guest.id"
          >
            <td>{{ displayName(guest) }}</td>
            <td>{{ guest.email || '—' }}</td>
            <td>{{ guest.phone || '—' }}</td>
            <td>{{ guest.city || '—' }}</td>
            <td>{{ guest.blacklisted ? 'Yes' : 'No' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(guest)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(guest.id)"
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
            <!-- Mini dashboard -->
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of guest contact and status
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="guest-stat-tile guest-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Clear
                    </div>
                    <div class="text-h5">
                      {{ stats.clear }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="guest-stat-tile guest-stat-tile--danger pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Blacklisted
                    </div>
                    <div class="text-h5">
                      {{ stats.blacklisted }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="guest-stat-tile guest-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With email
                    </div>
                    <div class="text-h5">
                      {{ stats.withEmail }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="guest-stat-tile guest-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With phone
                    </div>
                    <div class="text-h5">
                      {{ stats.withPhone }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="guest-stat-tile guest-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          Incomplete contact
                        </div>
                        <div class="text-h5">
                          {{ stats.incompleteContact }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-error-circle"
                        size="28"
                        class="text-medium-emphasis"
                      />
                    </div>
                  </div>
                </VCol>
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Clear vs blacklisted
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!guests.loading"
                    title="No guests yet"
                    description="Counts and charts will appear once guests are loaded."
                  />
                </div>

                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    Contact coverage
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
                Booking-linked guest counts are UI placeholders until booking metrics are wired.
              </VAlert>
            </div>

            <!-- Filter panel -->
            <div v-else-if="panelMode === 'filter'">
              <VForm @submit.prevent>
                <BaseInput
                  v-model="filters.name"
                  label="Name contains"
                  class="mb-2"
                />
                <BaseInput
                  v-model="filters.email"
                  label="Email contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.blacklisted"
                  label="Blacklist status"
                  :items="blacklistFilterItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.country"
                  label="Country"
                  :items="countryOptions"
                  clearable
                  class="mb-2"
                />
                <BaseInput
                  v-model="filters.city"
                  label="City contains"
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

            <!-- Create / edit panel -->
            <div v-else>
              <AppAlert :errors="guests.errors" />
              <VForm
                class="guest-form"
                @submit.prevent="submit"
              >
                <BaseInput
                  v-model="form.guest_name"
                  label="Guest Name"
                  required
                  :error="guests.errors.guest_name"
                />
                <BaseInput
                  v-model="form.phone"
                  label="Phone"
                  :error="guests.errors.phone"
                />
                <BaseInput
                  v-model="form.email"
                  label="Email"
                  type="email"
                  :error="guests.errors.email"
                />
                <BaseInput
                  v-model="form.city"
                  label="City"
                  :error="guests.errors.city"
                />
                <BaseInput
                  v-model="form.state"
                  label="State"
                  :error="guests.errors.state"
                />
                <BaseInput
                  v-model="form.country"
                  label="Country"
                  :error="guests.errors.country"
                />
                <BaseSelect
                  v-model="form.corporate_travel_booking_agent"
                  label="Corporate Travel Booking Agent"
                  :items="corporateAgentItems"
                  :error="guests.errors.corporate_travel_booking_agent"
                />
                <BaseInput
                  v-model="form.corporate_travel_booking_agent_name_email"
                  label="Corporate Travel Booking Agent Name (Email)"
                  type="email"
                  :error="guests.errors.corporate_travel_booking_agent_name_email"
                />
                <BaseInput
                  v-model="form.corporate_travel_booking_agent_first_name"
                  label="Corporate Travel Booking Agent Name (First)"
                  :error="guests.errors.corporate_travel_booking_agent_first_name"
                />
                <BaseInput
                  v-model="form.corporate_travel_booking_agent_last_name"
                  label="Corporate Travel Booking Agent Name (Last)"
                  :error="guests.errors.corporate_travel_booking_agent_last_name"
                />
                <BaseInput
                  v-model="form.corporate_business_name"
                  label="Corporate / Business Name"
                  :error="guests.errors.corporate_business_name"
                />
                <BaseTextarea
                  v-model="form.notes"
                  label="Notes"
                  :error="guests.errors.notes"
                />
                <div class="d-flex flex-wrap gap-2 guest-form__actions">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update guest' : 'Create guest'"
                    :loading="guests.loading"
                  />
                  <BaseButton
                    type="button"
                    variant="tonal"
                    label="Cancel"
                    @click="cancelCreate"
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
      title="Delete guest?"
      message="This soft-deletes the guest (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="guests.loading"
      @confirm="onDelete"
    />
  </div>
</template>

<style scoped>
.guest-form :deep(.v-input) {
  margin-bottom: 1rem;
}

.guest-form__actions {
  margin-top: 0.25rem;
}

.guest-stat-tile {
  border: 1px solid rgba(75, 70, 92, 0.08);
  background: rgba(75, 70, 92, 0.03);
}

.guest-stat-tile--success {
  background: rgba(40, 199, 111, 0.08);
}

.guest-stat-tile--danger {
  background: rgba(234, 84, 85, 0.1);
}

.guest-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.guest-stat-tile--info {
  background: rgba(0, 207, 232, 0.1);
}

.guest-stat-tile--warning {
  background: rgba(255, 159, 67, 0.1);
}
</style>
