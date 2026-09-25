<script setup>
const countries = useCountriesStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  name: '',
  code: '',
  lat: 0,
  lng: 0,
})

const filters = reactive({
  name: '',
  code: '',
  has_states: null,
})

const stateForm = reactive({
  name: '',
  code: '',
  lat: 0,
  lng: 0,
})

const editingId = ref(null)
const editingStateId = ref(null)
const selectedCountryId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = countries.data?.data || countries.data || []

  return Array.isArray(data) ? data : []
})

const stateCount = country => Array.isArray(country.states) ? country.states.length : 0

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()
  const codeQuery = filters.code.trim().toLowerCase()

  return allRows.value.filter(country => {
    if (nameQuery) {
      const name = String(country.name || '').toLowerCase()
      if (!name.includes(nameQuery))
        return false
    }

    if (codeQuery) {
      const code = String(country.code || '').toLowerCase()
      if (!code.includes(codeQuery))
        return false
    }

    if (filters.has_states !== null && filters.has_states !== undefined && filters.has_states !== '') {
      const hasStates = stateCount(country) > 0
      if (filters.has_states === 'yes' && !hasStates)
        return false
      if (filters.has_states === 'no' && hasStates)
        return false
    }

    return true
  })
})

const selectedCountry = computed(() => allRows.value.find(row => row.id === selectedCountryId.value) || null)
const states = computed(() => selectedCountry.value?.states || [])

const hasStatesFilterItems = [
  { title: 'All countries', value: null },
  { title: 'With states', value: 'yes' },
  { title: 'Without states', value: 'no' },
]

const stats = computed(() => {
  const list = allRows.value
  const withStates = list.filter(country => stateCount(country) > 0)
  const withoutStates = list.filter(country => stateCount(country) === 0)
  const totalStates = list.reduce((sum, country) => sum + stateCount(country), 0)
  const withCoords = list.filter(country => Number(country.lat) !== 0 || Number(country.lng) !== 0)
  const withCode = list.filter(country => Boolean(country.code))

  return {
    total: list.length,
    withStates: withStates.length,
    withoutStates: withoutStates.length,
    totalStates,
    withCoords: withCoords.length,
    withCode: withCode.length,
  }
})

const statusChartSeries = computed(() => [stats.value.withStates, stats.value.withoutStates])

const statusChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  labels: ['With states', 'Without'],
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
    y: { formatter: value => `${value} countries` },
  },
}))

const activityChartSeries = computed(() => ([
  {
    name: 'Counts',
    data: [
      stats.value.total,
      stats.value.totalStates,
      stats.value.withCoords,
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
    categories: ['Countries', 'States', 'Coords'],
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
    y: { formatter: value => String(value) },
  },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter countries'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit country' : 'Create country'

  return 'Country overview'
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
    code: '',
    has_states: null,
  })
}

onMounted(() => countries.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    name: '',
    code: '',
    lat: 0,
    lng: 0,
  })
}

const resetStateForm = () => {
  editingStateId.value = null
  Object.assign(stateForm, {
    name: '',
    code: '',
    lat: 0,
    lng: 0,
  })
  countries.stateErrors = {}
}

const edit = country => {
  editingId.value = country.id
  selectedCountryId.value = country.id
  Object.assign(form, {
    name: country.name ?? '',
    code: country.code ?? '',
    lat: country.lat ?? 0,
    lng: country.lng ?? 0,
  })
  resetStateForm()
  panelMode.value = 'create'
}

const selectCountry = country => {
  selectedCountryId.value = country.id
  resetStateForm()
}

const cancelCreate = () => {
  resetForm()
  panelMode.value = 'dashboard'
}

const submit = async () => {
  try {
    const payload = {
      name: form.name,
      code: form.code,
      lat: form.lat === '' ? 0 : Number(form.lat),
      lng: form.lng === '' ? 0 : Number(form.lng),
    }

    if (editingId.value)
      await countries.update(editingId.value, payload)
    else
      await countries.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
    await countries.load()
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
    await countries.remove(confirmDelete.id)
    if (selectedCountryId.value === confirmDelete.id)
      selectedCountryId.value = null
    confirmDelete.open = false
    await countries.load()
  }
  catch {
    // Store exposes errors.
  }
}

const editState = state => {
  editingStateId.value = state.id
  Object.assign(stateForm, {
    name: state.name ?? '',
    code: state.code ?? '',
    lat: state.lat ?? 0,
    lng: state.lng ?? 0,
  })
}

const submitState = async () => {
  if (!selectedCountryId.value)
    return

  try {
    const payload = {
      name: stateForm.name,
      code: stateForm.code,
      lat: stateForm.lat === '' ? 0 : Number(stateForm.lat),
      lng: stateForm.lng === '' ? 0 : Number(stateForm.lng),
    }

    if (editingStateId.value)
      await countries.updateState(selectedCountryId.value, editingStateId.value, payload)
    else
      await countries.createState(selectedCountryId.value, payload)

    resetStateForm()
    await countries.load()
  }
  catch {
    // Store exposes stateErrors.
  }
}

const removeState = async stateId => {
  if (!selectedCountryId.value)
    return

  try {
    await countries.removeState(selectedCountryId.value, stateId)
    await countries.load()
  }
  catch {
    // Store exposes stateErrors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Countries & States"
      subtitle="Manage countries and their states"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All countries"
          :loading="countries.loading"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No countries found"
          :empty-description="allRows.length && rows.length === 0 ? 'No countries match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Name</th>
                <th>Code</th>
                <th>States</th>
                <th>Coords</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="country in rows"
            :key="country.id"
            :class="{ 'bg-grey-lighten-4': selectedCountryId === country.id }"
          >
            <td>{{ country.name }}</td>
            <td>{{ country.code || '—' }}</td>
            <td>{{ stateCount(country) }}</td>
            <td>{{ country.lat }}, {{ country.lng }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="States"
                class="me-2"
                @click="selectCountry(country)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(country)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(country.id)"
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
                Snapshot of countries and state coverage
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With states
                    </div>
                    <div class="text-h5">
                      {{ stats.withStates }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Without
                    </div>
                    <div class="text-h5">
                      {{ stats.withoutStates }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Countries
                    </div>
                    <div class="text-h5">
                      {{ stats.total }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With coords
                    </div>
                    <div class="text-h5">
                      {{ stats.withCoords }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          Total states
                        </div>
                        <div class="text-h5">
                          {{ stats.totalStates }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-map-alt"
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
                    State coverage
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!countries.loading"
                    title="No countries yet"
                    description="Counts and charts will appear once countries are loaded."
                  />
                </div>

                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    Totals
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
                  v-model="filters.name"
                  label="Name contains"
                  class="mb-2"
                />
                <BaseInput
                  v-model="filters.code"
                  label="Code contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.has_states"
                  label="States"
                  :items="hasStatesFilterItems"
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

            <div v-else>
              <AppAlert :errors="countries.errors" />
              <VForm @submit.prevent="submit">
                <BaseInput
                  v-model="form.name"
                  label="Name"
                  :error="countries.errors.name"
                />
                <BaseInput
                  v-model="form.code"
                  label="Code"
                  :error="countries.errors.code"
                />
                <BaseInput
                  v-model="form.lat"
                  label="Latitude"
                  type="number"
                  :error="countries.errors.lat"
                />
                <BaseInput
                  v-model="form.lng"
                  label="Longitude"
                  type="number"
                  :error="countries.errors.lng"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update country' : 'Create country'"
                    :loading="countries.loading"
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

    <VCard
      v-if="selectedCountry"
      class="mt-6"
    >
      <VCardItem>
        <VCardTitle>States for {{ selectedCountry.name }}</VCardTitle>
      </VCardItem>
      <VCardText>
        <AppAlert :errors="countries.stateErrors" />
        <VRow>
          <VCol
            cols="12"
            md="4"
          >
            <VForm @submit.prevent="submitState">
              <BaseInput
                v-model="stateForm.name"
                label="State name"
                :error="countries.stateErrors.name"
              />
              <BaseInput
                v-model="stateForm.code"
                label="State code"
                :error="countries.stateErrors.code"
              />
              <BaseInput
                v-model="stateForm.lat"
                label="Latitude"
                type="number"
                :error="countries.stateErrors.lat"
              />
              <BaseInput
                v-model="stateForm.lng"
                label="Longitude"
                type="number"
                :error="countries.stateErrors.lng"
              />
              <div class="d-flex flex-wrap gap-2 mt-2">
                <BaseButton
                  type="submit"
                  :label="editingStateId ? 'Update state' : 'Add state'"
                  :loading="countries.loading"
                />
                <BaseButton
                  v-if="editingStateId"
                  type="button"
                  variant="tonal"
                  label="Cancel"
                  @click="resetStateForm"
                />
              </div>
            </VForm>
          </VCol>
          <VCol
            cols="12"
            md="8"
          >
            <DataTableShell
              title="States"
              :empty="states.length === 0"
              :empty-colspan="5"
              empty-title="No states for this country"
            >
              <template #head>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Lat</th>
                    <th>Lng</th>
                    <th>Actions</th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="state in states"
                :key="state.id"
              >
                <td>{{ state.name }}</td>
                <td>{{ state.code }}</td>
                <td>{{ state.lat }}</td>
                <td>{{ state.lng }}</td>
                <td class="text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="editState(state)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="removeState(state.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete country?"
      message="This deletes the country and soft-deletes its states."
      confirm-label="Delete"
      confirm-color="error"
      :loading="countries.loading"
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

.entity-stat-tile--muted {
  background: rgba(168, 170, 174, 0.12);
}

.entity-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.entity-stat-tile--info {
  background: rgba(0, 207, 232, 0.1);
}

.entity-stat-tile--warning {
  background: rgba(255, 159, 67, 0.1);
}
</style>
