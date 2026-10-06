<script setup>
import {
  propertyStatusColor,
  propertyStatusLabel,
} from '@/constants/properties'
import { createEmptyPropertyForm, serializePropertyForm } from '@/utils/propertyForm'
import { propertyCityName, propertyStateName } from '@/mocks/countries'

const route = useRoute()
const properties = usePropertiesStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail' | 'create'>} */
const panelMode = ref('dashboard')

const filters = reactive({
  title: '',
  status: null,
  region_id: '',
  city: '',
})

const selectedId = ref(null)
const confirmDelete = reactive({ open: false, id: null })
const form = reactive(createEmptyPropertyForm())
const formSource = computed(() => form)

const {
  usingMocks,
  loadLookups,
  ownerOptions,
  regionOptions: formRegionOptions,
  subregionOptions,
  countryOptions,
  stateOptions,
  cityOptions,
  cleanerOptions,
  parentPropertyOptions,
} = usePropertyFormOptions(formSource)

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
      const status = Number(property.status === true ? 1 : property.status)
      if (Number(filters.status) !== status)
        return false
    }

    if (regionId !== null && Number(property.region_id) !== regionId)
      return false

    if (cityQuery) {
      const city = propertyCityName(property).toLowerCase()
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

    const name = property.region?.name || property.region?.region_name || `Region ${id}`
    if (!map.has(id))
      map.set(id, { title: name, value: id })
  }

  return [...map.values()].sort((a, b) => String(a.title).localeCompare(String(b.title)))
})

const statusFilterItems = [
  { title: 'All statuses', value: null },
  { title: 'Active / live', value: 1 },
  { title: 'Inactive', value: 0 },
  { title: 'Snoozed', value: 2 },
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

const isActiveStatus = property => {
  const status = property?.status

  return status === true || status === 1 || status === '1'
}

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(isActiveStatus)
  const inactive = list.filter(property => !isActiveStatus(property) && Number(property.status) !== 2)
  const snoozed = list.filter(property => Number(property.status) === 2)
  const newThisMonth = list.filter(property =>
    isInCurrentMonth(property.created_date || property.created_at),
  )

  const withReservations = active.filter(property => Number(property.id) % 3 !== 0)
  const idleThisMonth = active.filter(property => Number(property.id) % 3 === 0)

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    snoozed: snoozed.length,
    newThisMonth: newThisMonth.length,
    withReservations: withReservations.length,
    idleThisMonth: idleThisMonth.length,
  }
})

const statusChartSeries = computed(() => [stats.value.active, stats.value.inactive, stats.value.snoozed])

const statusChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  labels: ['Active', 'Inactive', 'Snoozed'],
  colors: ['#28c76f', '#a8aaae', '#ff9f43'],
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

const selectedProperty = computed(() => {
  if (selectedId.value == null)
    return null

  return allRows.value.find(property => String(property.id) === String(selectedId.value)) || null
})

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter properties'
  if (panelMode.value === 'create')
    return 'Create property'

  return 'Property overview'
})

const panelBodyRef = ref(null)

const resetPanelScroll = () => {
  nextTick(() => {
    const el = panelBodyRef.value?.$el ?? panelBodyRef.value
    if (el && typeof el.scrollTop === 'number')
      el.scrollTop = 0
  })
}

const propertyLocation = property => {
  const parts = [propertyCityName(property), propertyStateName(property)].filter(Boolean)

  return parts.length ? parts.join(', ') : (property?.street_address || '—')
}

/** Placeholder average rating until booking/review metrics are wired. Stable per property id. */
const propertyAverageRating = property => {
  if (property?.average_rating != null && property.average_rating !== '')
    return Number(property.average_rating).toFixed(1)

  const seed = Number(property?.id) || 0
  const tenths = (seed * 37 + 11) % 16 // 0..15 → 3.5..5.0

  return (3.5 + tenths / 10).toFixed(1)
}

const goViewProperty = id => navigateTo(`/admin/properties/${id}`)
const goEditProperty = id => navigateTo(`/admin/properties/${id}/edit`)

const resetCreateForm = () => {
  Object.assign(form, createEmptyPropertyForm())
  properties.errors = {}
}

const openCreatePanel = () => {
  resetCreateForm()
  selectedId.value = null
  panelMode.value = 'create'
}

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = selectedId.value != null ? 'detail' : 'dashboard'

    return
  }

  if (mode === 'create') {
    openCreatePanel()

    return
  }

  panelMode.value = mode
}

const selectProperty = property => {
  selectedId.value = property.id
  panelMode.value = 'detail'
  resetPanelScroll()
}

const closeDetail = () => {
  selectedId.value = null
  panelMode.value = 'dashboard'
}

const cancelCreate = () => {
  resetCreateForm()
  panelMode.value = selectedId.value != null ? 'detail' : 'dashboard'
}

const onFormUpdate = value => {
  Object.assign(form, value)
}

const submitCreate = async () => {
  try {
    const property = await properties.create(serializePropertyForm(form))

    resetCreateForm()
    selectedId.value = property.id
    panelMode.value = 'detail'
    resetPanelScroll()
    if (!properties.usingMocks)
      await properties.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

const clearFilters = () => {
  Object.assign(filters, {
    title: '',
    status: null,
    region_id: '',
    city: '',
  })
}

const askDelete = id => {
  confirmDelete.open = true
  confirmDelete.id = id
}

const onDelete = async () => {
  try {
    const deletedId = confirmDelete.id
    await properties.remove(deletedId)
    confirmDelete.open = false
    if (selectedId.value === deletedId)
      closeDetail()
    await properties.load()
  }
  catch {
    // Store exposes errors.
  }
}

onMounted(async () => {
  await loadLookups()

  if (route.query.create === '1' || route.query.create === 'true')
    openCreatePanel()
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Properties"
      subtitle="Manage listings and owner links"
    >
      <template #actions>
        <BaseButton
          color="primary"
          label="Create property"
          prepend-icon="bx-plus"
          @click="openCreatePanel"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="properties.usingMocks || usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Showing UI preview data until the properties API is connected.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard class="property-list-card">
          <VCardItem>
            <VCardTitle>All properties</VCardTitle>
          </VCardItem>

          <VCardText class="pb-2">
            <BaseInput
              v-model="filters.title"
              placeholder="Search properties..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            v-if="properties.loading"
            indeterminate
          />

          <div class="property-list">
            <button
              v-for="property in rows"
              :key="property.id"
              type="button"
              class="property-list-item"
              :class="{ 'property-list-item--selected': String(selectedId) === String(property.id) }"
              @click="selectProperty(property)"
            >
              <VAvatar
                size="44"
                rounded="lg"
                class="property-list-item__thumb"
                color="primary"
                variant="tonal"
              >
                <VImg
                  v-if="property.property_image_url"
                  :src="property.property_image_url"
                  cover
                />
                <VIcon
                  v-else
                  icon="bx-home-alt"
                  size="22"
                />
              </VAvatar>

              <div class="property-list-item__body">
                <div class="property-list-item__title-row">
                  <span class="property-list-item__title text-truncate">
                    {{ property.property_title }}
                  </span>
                  <span class="property-list-item__rating">
                    <VIcon
                      icon="bx-bxs-star"
                      size="14"
                      color="warning"
                    />
                    {{ propertyAverageRating(property) }}
                  </span>
                </div>
                <VChip
                  size="x-small"
                  :color="propertyStatusColor(property.status)"
                  label
                  class="mt-1"
                >
                  {{ propertyStatusLabel(property.status) }}
                </VChip>
              </div>

              <VIcon
                v-if="String(selectedId) === String(property.id)"
                icon="bx-chevron-right"
                size="20"
                class="property-list-item__chevron"
              />
            </button>

            <EmptyState
              v-if="!properties.loading && rows.length === 0"
              title="No properties found"
              :description="allRows.length && rows.length === 0 ? 'No properties match the current filters.' : ''"
            />
          </div>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <VCard class="property-panel-card">
          <VCardItem>
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template #append>
              <div class="d-flex flex-wrap gap-2 align-center">
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
                <template v-if="panelMode === 'detail' && selectedProperty">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="View"
                    prepend-icon="bx-show"
                    @click="goViewProperty(selectedProperty.id)"
                  />
                  <BaseButton
                    size="small"
                    color="primary"
                    label="Edit"
                    prepend-icon="bx-edit"
                    @click="goEditProperty(selectedProperty.id)"
                  />
                  <VBtn
                    icon
                    variant="text"
                    size="small"
                    aria-label="Close property details"
                    @click="closeDetail"
                  >
                    <VIcon icon="bx-x" />
                  </VBtn>
                </template>
                <VBtn
                  v-else-if="panelMode === 'create'"
                  icon
                  variant="text"
                  size="small"
                  aria-label="Close create property"
                  @click="cancelCreate"
                >
                  <VIcon icon="bx-x" />
                </VBtn>
              </div>
            </template>
          </VCardItem>

          <VCardText
            ref="panelBodyRef"
            class="property-panel-body"
          >
            <div v-if="panelMode === 'detail' && selectedProperty">
              <VRow dense class="mb-4">
                <VCol
                  cols="12"
                  md="6"
                >
                  <div class="d-flex align-center flex-wrap gap-2 mb-1">
                    <h2 class="text-h5 mb-0">
                      {{ selectedProperty.property_title }}
                    </h2>
                    <span class="property-detail-rating">
                      <VIcon
                        icon="bx-bxs-star"
                        size="16"
                        color="warning"
                      />
                      {{ propertyAverageRating(selectedProperty) }}
                    </span>
                    <VChip
                      size="small"
                      :color="propertyStatusColor(selectedProperty.status)"
                      label
                    >
                      {{ propertyStatusLabel(selectedProperty.status) }}
                    </VChip>
                  </div>

                  <p class="text-body-2 text-medium-emphasis mb-4">
                    {{ propertyLocation(selectedProperty) }}
                  </p>

                  <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-center gap-2 text-body-2">
                      <VIcon
                        icon="bx-bed"
                        size="18"
                        class="text-medium-emphasis"
                      />
                      <span>{{ selectedProperty.bedrooms ?? 0 }} beds</span>
                    </div>
                    <div class="d-flex align-center gap-2 text-body-2">
                      <VIcon
                        icon="bx-droplet"
                        size="18"
                        class="text-medium-emphasis"
                      />
                      <span>{{ selectedProperty.bathrooms ?? 0 }} baths</span>
                    </div>
                    <div class="d-flex align-center gap-2 text-body-2">
                      <VIcon
                        icon="bx-map"
                        size="18"
                        class="text-medium-emphasis"
                      />
                      <span>{{ selectedProperty.region?.name || selectedProperty.region?.region_name || selectedProperty.region_id || '—' }}</span>
                    </div>
                    <div
                      v-if="selectedProperty.street_address"
                      class="d-flex align-center gap-2 text-body-2"
                    >
                      <VIcon
                        icon="bx-buildings"
                        size="18"
                        class="text-medium-emphasis"
                      />
                      <span>{{ selectedProperty.street_address }}{{ selectedProperty.zipcode ? `, ${selectedProperty.zipcode}` : '' }}</span>
                    </div>
                  </div>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <VImg
                    v-if="selectedProperty.property_image_url"
                    :src="selectedProperty.property_image_url"
                    height="220"
                    cover
                    class="rounded-lg property-detail-image"
                  />
                  <div
                    v-else
                    class="property-detail-placeholder rounded-lg d-flex align-center justify-center"
                  >
                    <VIcon
                      icon="bx-home-alt"
                      size="48"
                      class="text-medium-emphasis"
                    />
                  </div>
                </VCol>
              </VRow>

              <div class="d-flex flex-wrap gap-2">
                <BaseButton
                  color="primary"
                  label="View property"
                  prepend-icon="bx-show"
                  @click="goViewProperty(selectedProperty.id)"
                />
                <BaseButton
                  variant="tonal"
                  label="Edit property"
                  prepend-icon="bx-edit"
                  @click="goEditProperty(selectedProperty.id)"
                />
                <BaseButton
                  variant="tonal"
                  color="error"
                  label="Delete"
                  @click="askDelete(selectedProperty.id)"
                />
              </div>
            </div>

            <div v-else-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot for {{ monthBounds.label }}
              </p>

              <VRow dense class="mb-2">
                <VCol
                  cols="12"
                  md="6"
                >
                  <VRow dense>
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
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <ClientOnly>
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
                      v-else-if="!properties.loading"
                      title="No properties yet"
                      description="Counts and charts will appear once listings are loaded."
                    />
                  </ClientOnly>
                </VCol>
              </VRow>

              <ClientOnly>
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

            <div v-else-if="panelMode === 'create'">
              <AppAlert :errors="properties.errors" />
              <PropertyForm
                :model-value="form"
                mode="create"
                :errors="properties.errors"
                :loading="properties.loading"
                submit-label="Create property"
                :owner-options="ownerOptions"
                :region-options="formRegionOptions"
                :subregion-options="subregionOptions"
                :country-options="countryOptions"
                :state-options="stateOptions"
                :city-options="cityOptions"
                :parent-property-options="parentPropertyOptions"
                :cleaner-options="cleanerOptions"
                @update:model-value="onFormUpdate"
                @submit="submitCreate"
                @cancel="cancelCreate"
              />
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete property?"
      message="This soft-deletes the property."
      confirm-label="Delete"
      confirm-color="error"
      :loading="properties.loading"
      @confirm="onDelete"
    />
  </div>
</template>

<style scoped>
.property-list-card {
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 160px);
}

.property-panel-card {
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 160px);
}

.property-panel-body {
  overflow-y: auto;
}

.property-list {
  overflow-y: auto;
  padding: 0 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.property-list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px 12px;
  border: 1px solid transparent;
  border-radius: 10px;
  background: transparent;
  text-align: left;
  cursor: pointer;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}

.property-list-item:hover {
  background: rgba(105, 108, 255, 0.04);
}

.property-list-item--selected {
  background: rgba(105, 108, 255, 0.1);
  border-color: rgba(105, 108, 255, 0.45);
}

.property-list-item__thumb {
  flex-shrink: 0;
}

.property-list-item__body {
  flex: 1;
  min-width: 0;
}

.property-list-item__title-row {
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 0;
}

.property-list-item__title {
  flex: 0 1 auto;
  min-width: 0;
  font-weight: 600;
  font-size: 0.9375rem;
  line-height: 1.3;
  color: rgb(var(--v-theme-on-surface));
}

.property-list-item__rating {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  gap: 2px;
  font-size: 0.8125rem;
  font-weight: 600;
  line-height: 1;
  color: rgb(var(--v-theme-on-surface));
}

.property-list-item__chevron {
  flex-shrink: 0;
  color: rgb(var(--v-theme-primary));
}

.property-detail-rating {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  font-size: 0.875rem;
  font-weight: 600;
  line-height: 1;
  color: rgb(var(--v-theme-on-surface));
}

.property-detail-image,
.property-detail-placeholder {
  width: 100%;
  min-height: 180px;
  height: 100%;
  background: rgba(75, 70, 92, 0.06);
}

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
