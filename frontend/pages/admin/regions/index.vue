<script setup>
const regions = useRegionsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  region_name: '',
  shortcode: '',
  color: '',
})

const filters = reactive({
  name: '',
  shortcode: '',
  has_subregions: null,
})

const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = regions.data?.data || regions.data || []

  return Array.isArray(data) ? data : []
})

const subregionCount = region => Array.isArray(region.subregions) ? region.subregions.length : 0

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()
  const shortcodeQuery = filters.shortcode.trim().toLowerCase()

  return allRows.value.filter(region => {
    if (nameQuery) {
      const name = String(region.region_name || '').toLowerCase()
      if (!name.includes(nameQuery))
        return false
    }

    if (shortcodeQuery) {
      const shortcode = String(region.shortcode || '').toLowerCase()
      if (!shortcode.includes(shortcodeQuery))
        return false
    }

    if (filters.has_subregions !== null && filters.has_subregions !== undefined && filters.has_subregions !== '') {
      const hasSubs = subregionCount(region) > 0
      if (filters.has_subregions === 'yes' && !hasSubs)
        return false
      if (filters.has_subregions === 'no' && hasSubs)
        return false
    }

    return true
  })
})

const subregionFilterItems = [
  { title: 'All regions', value: null },
  { title: 'With subregions', value: 'yes' },
  { title: 'Without subregions', value: 'no' },
]

const stats = computed(() => {
  const list = allRows.value
  const withShortcode = list.filter(region => Boolean(region.shortcode))
  const withColor = list.filter(region => Boolean(region.color))
  const withSubregions = list.filter(region => subregionCount(region) > 0)
  const withoutSubregions = list.filter(region => subregionCount(region) === 0)
  const totalSubregions = list.reduce((sum, region) => sum + subregionCount(region), 0)

  return {
    total: list.length,
    withShortcode: withShortcode.length,
    withColor: withColor.length,
    withSubregions: withSubregions.length,
    withoutSubregions: withoutSubregions.length,
    totalSubregions,
  }
})

const statusChartSeries = computed(() => [stats.value.withSubregions, stats.value.withoutSubregions])

const statusChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  labels: ['With subregions', 'Without'],
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
    y: { formatter: value => `${value} regions` },
  },
}))

const activityChartSeries = computed(() => ([
  {
    name: 'Regions',
    data: [
      stats.value.withShortcode,
      stats.value.withColor,
      stats.value.totalSubregions,
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
    categories: ['Shortcode', 'Color', 'Subregions'],
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
    return 'Filter regions'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit region' : 'Create region'

  return 'Region overview'
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
    shortcode: '',
    has_subregions: null,
  })
}

onMounted(() => regions.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    region_name: '',
    shortcode: '',
    color: '',
  })
}

const edit = region => {
  editingId.value = region.id
  Object.assign(form, {
    region_name: region.region_name || '',
    shortcode: region.shortcode || '',
    color: region.color || '',
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
      shortcode: form.shortcode || null,
      color: form.color || null,
    }

    if (editingId.value)
      await regions.update(editingId.value, payload)
    else
      await regions.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
    await regions.load()
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
    await regions.remove(confirmDelete.id)
    confirmDelete.open = false
    await regions.load()
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
      title="Regions"
      subtitle="Manage property regions"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All regions"
          :loading="regions.loading"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No regions found"
          :empty-description="allRows.length && rows.length === 0 ? 'No regions match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Region name</th>
                <th>Shortcode</th>
                <th>Color</th>
                <th>Subregions</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="region in rows"
            :key="region.id"
          >
            <td>{{ region.region_name }}</td>
            <td>{{ region.shortcode || '—' }}</td>
            <td>
              <span
                v-if="region.color"
                class="d-inline-flex align-center gap-2"
              >
                <span
                  class="region-color-swatch"
                  :style="{ backgroundColor: region.color }"
                />
                {{ region.color }}
              </span>
              <template v-else>
                —
              </template>
            </td>
            <td>{{ subregionCount(region) }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(region)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(region.id)"
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
                Snapshot of regions and subregion coverage
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With subregions
                    </div>
                    <div class="text-h5">
                      {{ stats.withSubregions }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Without
                    </div>
                    <div class="text-h5">
                      {{ stats.withoutSubregions }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With shortcode
                    </div>
                    <div class="text-h5">
                      {{ stats.withShortcode }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With color
                    </div>
                    <div class="text-h5">
                      {{ stats.withColor }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          Total subregions
                        </div>
                        <div class="text-h5">
                          {{ stats.totalSubregions }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-git-branch"
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
                    Subregion coverage
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!regions.loading"
                    title="No regions yet"
                    description="Counts and charts will appear once regions are loaded."
                  />
                </div>

                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    Metadata
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
                  v-model="filters.shortcode"
                  label="Shortcode contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.has_subregions"
                  label="Subregions"
                  :items="subregionFilterItems"
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
              <AppAlert :errors="regions.errors" />
              <VForm @submit.prevent="submit">
                <BaseInput
                  v-model="form.region_name"
                  label="Region name"
                  :error="regions.errors.region_name"
                />
                <BaseInput
                  v-model="form.shortcode"
                  label="Shortcode"
                  :error="regions.errors.shortcode"
                />
                <BaseInput
                  v-model="form.color"
                  label="Color"
                  :error="regions.errors.color"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update region' : 'Create region'"
                    :loading="regions.loading"
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
      title="Delete region?"
      message="This soft-deletes the region (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="regions.loading"
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

.region-color-swatch {
  display: inline-block;
  width: 14px;
  height: 14px;
  border-radius: 3px;
  border: 1px solid rgba(75, 70, 92, 0.2);
}
</style>
