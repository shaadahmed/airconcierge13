<script setup>
const properties = usePropertiesStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  property_title: '',
  region_id: '',
  subregion_id: '',
  hostaway_listing_id: '',
  street_address: '',
  city: '',
  state: '',
  zipcode: '',
  status: true,
})

const filters = reactive({
  title: '',
  status: null,
  region_id: '',
  city: '',
})

const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = properties.data?.data || properties.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const titleQuery = filters.title.trim().toLowerCase()
  const regionId = filters.region_id === '' || filters.region_id === null
    ? null
    : Number(filters.region_id)
  const cityQuery = filters.city.trim().toLowerCase()

  return allRows.value.filter(property => {
    if (titleQuery) {
      const title = String(property.property_title || '').toLowerCase()
      if (!title.includes(titleQuery))
        return false
    }

    if (filters.status !== null && filters.status !== undefined && filters.status !== '') {
      const isActive = Boolean(property.status)
      if (filters.status === 'active' && !isActive)
        return false
      if (filters.status === 'inactive' && isActive)
        return false
    }

    if (regionId !== null && Number(property.region_id) !== regionId)
      return false

    if (cityQuery) {
      const city = String(property.city || '').toLowerCase()
      if (!city.includes(cityQuery))
        return false
    }

    return true
  })
})

const regionOptions = computed(() => {
  const map = new Map()

  for (const property of allRows.value) {
    const id = property.region_id
    if (id == null)
      continue

    const name = property.region?.name || `Region ${id}`
    if (!map.has(id))
      map.set(id, { title: name, value: id })
  }

  return [...map.values()].sort((a, b) => String(a.title).localeCompare(String(b.title)))
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

const parsePropertyDate = value => {
  if (!value)
    return null

  const date = new Date(value)

  return Number.isNaN(date.getTime()) ? null : date
}

const isInCurrentMonth = value => {
  const date = parsePropertyDate(value)
  if (!date)
    return false

  const { start, end } = monthBounds.value

  return date >= start && date <= end
}

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(property => Boolean(property.status))
  const inactive = list.filter(property => !property.status)
  const newThisMonth = list.filter(property =>
    isInCurrentMonth(property.created_date || property.created_at),
  )

  // UI preview until reservation metrics are available from the API.
  // Deterministic split of active properties so charts stay stable across reloads.
  const withReservations = active.filter(property => Number(property.id) % 3 !== 0)
  const idleThisMonth = active.filter(property => Number(property.id) % 3 === 0)

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    newThisMonth: newThisMonth.length,
    withReservations: withReservations.length,
    idleThisMonth: idleThisMonth.length,
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
      stats.value.newThisMonth,
      stats.value.withReservations,
      stats.value.idleThisMonth,
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
    categories: ['New', 'Reserved', 'Idle'],
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
    return 'Filter properties'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit property' : 'Create property'

  return 'Property overview'
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
    title: '',
    status: null,
    region_id: '',
    city: '',
  })
}

onMounted(() => properties.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    property_title: '',
    region_id: '',
    subregion_id: '',
    hostaway_listing_id: '',
    street_address: '',
    city: '',
    state: '',
    zipcode: '',
    status: true,
  })
}

const edit = property => {
  editingId.value = property.id
  Object.assign(form, {
    property_title: property.property_title || '',
    region_id: property.region_id || '',
    subregion_id: property.subregion_id || '',
    hostaway_listing_id: property.hostaway_listing_id || '',
    street_address: property.street_address || '',
    city: property.city || '',
    state: property.state || '',
    zipcode: property.zipcode || '',
    status: property.status ?? true,
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
      ...form,
      region_id: form.region_id || null,
      subregion_id: form.subregion_id || null,
      hostaway_listing_id: form.hostaway_listing_id || null,
    }

    if (editingId.value)
      await properties.update(editingId.value, payload)
    else
      await properties.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
    await properties.load()
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
    await properties.remove(confirmDelete.id)
    confirmDelete.open = false
    await properties.load()
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
      title="Properties"
      subtitle="Manage listings and owner links"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All properties"
          :loading="properties.loading"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No properties found"
          :empty-description="allRows.length && rows.length === 0 ? 'No properties match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Title</th>
                <th>City</th>
                <th>Status</th>
                <th>Region</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="property in rows"
            :key="property.id"
          >
            <td>{{ property.property_title }}</td>
            <td>{{ property.city || '—' }}</td>
            <td>{{ property.status ? 'Live' : 'Inactive' }}</td>
            <td>{{ property.region?.name || property.region_id || '—' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(property)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(property.id)"
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
                Snapshot for {{ monthBounds.label }}
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="property-stat-tile property-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Active
                    </div>
                    <div class="text-h5">
                      {{ stats.active }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="property-stat-tile property-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Inactive
                    </div>
                    <div class="text-h5">
                      {{ stats.inactive }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="property-stat-tile property-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      New this month
                    </div>
                    <div class="text-h5">
                      {{ stats.newThisMonth }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="property-stat-tile property-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With reservations
                    </div>
                    <div class="text-h5">
                      {{ stats.withReservations }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="property-stat-tile property-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          Idle this month
                        </div>
                        <div class="text-h5">
                          {{ stats.idleThisMonth }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-time-five"
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
                    v-else-if="!properties.loading"
                    title="No properties yet"
                    description="Counts and charts will appear once listings are loaded."
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
                Reservation and idle figures are UI placeholders until booking metrics are wired.
              </VAlert>
            </div>

            <!-- Filter panel -->
            <div v-else-if="panelMode === 'filter'">
              <VForm @submit.prevent>
                <BaseInput
                  v-model="filters.title"
                  label="Title contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.status"
                  label="Status"
                  :items="statusFilterItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.region_id"
                  label="Region"
                  :items="regionOptions"
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
              <AppAlert :errors="properties.errors" />
              <VForm @submit.prevent="submit">
                <BaseInput
                  v-model="form.property_title"
                  label="Title"
                  :error="properties.errors.property_title"
                />
                <BaseInput
                  v-model="form.region_id"
                  label="Region ID"
                  type="number"
                  :error="properties.errors.region_id"
                />
                <BaseInput
                  v-model="form.subregion_id"
                  label="Subregion ID"
                  type="number"
                  :error="properties.errors.subregion_id"
                />
                <BaseInput
                  v-model="form.hostaway_listing_id"
                  label="Hostaway listing ID"
                  type="number"
                  :error="properties.errors.hostaway_listing_id"
                />
                <BaseInput
                  v-model="form.street_address"
                  label="Street address"
                  :error="properties.errors.street_address"
                />
                <BaseInput
                  v-model="form.city"
                  label="City"
                  :error="properties.errors.city"
                />
                <BaseInput
                  v-model="form.state"
                  label="State"
                  :error="properties.errors.state"
                />
                <BaseInput
                  v-model="form.zipcode"
                  label="Zipcode"
                  :error="properties.errors.zipcode"
                />
                <BaseCheckbox
                  v-model="form.status"
                  label="Active / live"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update property' : 'Create property'"
                    :loading="properties.loading"
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
      title="Delete property?"
      message="This soft-deletes the property (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="properties.loading"
      @confirm="onDelete"
    />
  </div>
</template>

<style scoped>
.property-stat-tile {
  border: 1px solid rgba(75, 70, 92, 0.08);
  background: rgba(75, 70, 92, 0.03);
}

.property-stat-tile--success {
  background: rgba(40, 199, 111, 0.08);
}

.property-stat-tile--muted {
  background: rgba(168, 170, 174, 0.12);
}

.property-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.property-stat-tile--info {
  background: rgba(0, 207, 232, 0.1);
}

.property-stat-tile--warning {
  background: rgba(255, 159, 67, 0.1);
}
</style>
