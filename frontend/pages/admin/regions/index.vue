<script setup>
const route = useRoute()
const regions = useRegionsStore()
const subregions = useSubregionsStore()

/** @type {import('vue').Ref<'region' | 'subregion'>} */
const panelEntity = ref('region')

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create' | 'detail'>} */
const panelMode = ref('dashboard')

const form = reactive({
  region_name: '',
  shortcode: '',
  color: '',
})

const emptySubregionForm = () => ({
  region_id: '',
  subregion_name: '',
  transient_occupancy_tax: '',
  short_stay_len: '',
  business_license_account: '',
  permit_no: '',
  permit_issue_date: '',
  permit_length: '',
  permit_expiry_date: '',
  limited_bookings: 'No',
  limit_type: '',
  bookings_limit: '',
  nights_limit: '',
  limit_filter: '',
})

const subregionForm = reactive(emptySubregionForm())

const filters = reactive({
  name: '',
  shortcode: '',
  has_subregions: null,
})

const editingId = ref(null)
const editingSubregionId = ref(null)
const selectedSubregionId = ref(null)
const expandedRegionId = ref(null)
const loadedRegionIds = ref(/** @type {Set<number>} */ (new Set()))
const confirmDelete = reactive({ open: false, id: null, type: 'region' })

const allRows = computed(() => {
  const data = regions.data?.data || regions.data || []

  return Array.isArray(data) ? data : []
})

const allSubregionRows = computed(() => {
  const data = subregions.data?.data || subregions.data || []

  return Array.isArray(data) ? data : []
})

const subregionsForRegion = regionId => {
  const id = Number(regionId)

  return allSubregionRows.value.filter(row => Number(row.region_id) === id)
}

const subregionCount = region => {
  const regionId = Number(region.id)
  const fromStore = subregionsForRegion(regionId)

  if (loadedRegionIds.value.has(regionId) || fromStore.length > 0)
    return fromStore.length

  return Array.isArray(region.subregions) ? region.subregions.length : 0
}

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

const selectedRegion = computed(() => allRows.value.find(row => Number(row.id) === Number(expandedRegionId.value)) || null)

const isSubregionView = computed(() => expandedRegionId.value != null)

const listTitle = computed(() => {
  if (selectedRegion.value)
    return `${selectedRegion.value.region_name}'s subregions`

  return 'All regions'
})

const listCountLabel = computed(() => {
  if (isSubregionView.value) {
    const count = activeSubregionRows.value.length

    return `${count} ${count === 1 ? 'subregion' : 'subregions'}`
  }

  const count = rows.value.length

  return `${count} ${count === 1 ? 'region' : 'regions'}`
})

const activeSubregionRows = computed(() => {
  if (!expandedRegionId.value)
    return []

  let list = subregionsForRegion(expandedRegionId.value)

  if (!list.length && Array.isArray(selectedRegion.value?.subregions))
    list = selectedRegion.value.subregions

  const nameQuery = filters.name.trim().toLowerCase()
  if (!nameQuery)
    return list

  return list.filter(row => {
    const name = String(row.subregion_name || row.name || '').toLowerCase()

    return name.includes(nameQuery)
  })
})

const selectedSubregion = computed(() => {
  if (selectedSubregionId.value == null)
    return null

  return allSubregionRows.value.find(row => Number(row.id) === Number(selectedSubregionId.value))
    || activeSubregionRows.value.find(row => Number(row.id) === Number(selectedSubregionId.value))
    || null
})

const subregionStats = computed(() => {
  const list = subregionsForRegion(expandedRegionId.value)
  const source = list.length
    ? list
    : (Array.isArray(selectedRegion.value?.subregions) ? selectedRegion.value.subregions : [])

  return {
    total: source.length,
    withTot: source.filter(row => row.transient_occupancy_tax !== null && row.transient_occupancy_tax !== undefined && row.transient_occupancy_tax !== '').length,
    withLimits: source.filter(row => row.limited_bookings === 'Yes' || Boolean(row.limit_type)).length,
    withLicense: source.filter(row => Number(row.business_license_account) === 1).length,
  }
})

const subregionFilterItems = [
  { title: 'All regions', value: null },
  { title: 'With subregions', value: 'yes' },
  { title: 'Without subregions', value: 'no' },
]

const shortStayItems = Array.from({ length: 31 }, (_, index) => ({
  title: String(index + 1),
  value: index + 1,
}))

const permitLengthItems = Array.from({ length: 24 }, (_, index) => ({
  title: `${index + 1} month${index + 1 > 1 ? 's' : ''}`,
  value: index + 1,
}))

const yesNoItems = [
  { title: 'Yes', value: 1 },
  { title: 'No', value: 0 },
]

const limitedBookingsItems = [
  { title: 'No', value: 'No' },
  { title: 'Yes', value: 'Yes' },
]

const limitTypeItems = [
  { title: 'Number of reservations', value: 'RESERVATIONS' },
  { title: 'Number of nights booked', value: 'NIGHTS' },
]

const limitFilterItems = [
  { title: 'Calendar year', value: 'CALENDAR' },
  { title: 'Permit year (start - end date of permit)', value: 'PERMIT_YEAR' },
]

const showLimitFields = computed(() => subregionForm.limited_bookings === 'Yes')
const showReservationLimit = computed(() => showLimitFields.value && subregionForm.limit_type === 'RESERVATIONS')
const showNightsLimit = computed(() => showLimitFields.value && subregionForm.limit_type === 'NIGHTS')

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

const panelTitle = computed(() => {
  if (panelEntity.value === 'subregion') {
    if (panelMode.value === 'create')
      return editingSubregionId.value ? 'Edit subregion' : 'Create subregion'
    if (panelMode.value === 'detail' && selectedSubregion.value)
      return selectedSubregion.value.subregion_name || selectedSubregion.value.name || 'Subregion details'

    return selectedRegion.value
      ? `${selectedRegion.value.region_name} overview`
      : 'Subregion overview'
  }

  if (panelMode.value === 'filter')
    return 'Filter regions'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit region' : 'Create region'

  return 'Region overview'
})

const limitLabel = row => {
  if (row.limited_bookings === 'Yes' || row.limit_type) {
    if (row.limit_type === 'RESERVATIONS')
      return `Reservations${row.bookings_limit || row.limit_value ? ` · ${row.bookings_limit || row.limit_value}` : ''}`
    if (row.limit_type === 'NIGHTS')
      return `Nights${row.nights_limit || row.limit_value ? ` · ${row.nights_limit || row.limit_value}` : ''}`

    return 'Limited'
  }

  return 'No limit'
}

const licenseLabel = row => {
  if (Number(row.business_license_account) === 1)
    return 'AC license'
  if (Number(row.business_license_account) === 0)
    return 'No license'

  return null
}

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = 'dashboard'

    return
  }

  if (mode === 'create') {
    panelEntity.value = 'region'
    if (panelMode.value !== 'create')
      resetForm()
  }

  panelMode.value = mode
}

const clearFilters = () => {
  Object.assign(filters, {
    name: '',
    shortcode: '',
    has_subregions: null,
  })
}

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    region_name: '',
    shortcode: '',
    color: '',
  })
}

const resetSubregionForm = () => {
  editingSubregionId.value = null
  Object.assign(subregionForm, emptySubregionForm())
  if (expandedRegionId.value)
    subregionForm.region_id = expandedRegionId.value
}

const computeExpiryDate = () => {
  if (!subregionForm.permit_issue_date || !subregionForm.permit_length)
    return

  const issue = new Date(subregionForm.permit_issue_date)
  if (Number.isNaN(issue.getTime()))
    return

  const expiry = new Date(issue)
  expiry.setMonth(expiry.getMonth() + Number(subregionForm.permit_length))
  subregionForm.permit_expiry_date = expiry.toISOString().slice(0, 10)
}

watch(() => [subregionForm.permit_issue_date, subregionForm.permit_length], () => {
  computeExpiryDate()
})

watch(() => subregionForm.limited_bookings, value => {
  if (value !== 'Yes') {
    subregionForm.limit_type = ''
    subregionForm.bookings_limit = ''
    subregionForm.nights_limit = ''
    subregionForm.limit_filter = ''
  }
})

watch(() => subregionForm.limit_type, value => {
  if (value !== 'RESERVATIONS')
    subregionForm.bookings_limit = ''
  if (value !== 'NIGHTS')
    subregionForm.nights_limit = ''
})

const loadSubregionsForRegion = async regionId => {
  const id = Number(regionId)
  await subregions.load()
  if (id)
    loadedRegionIds.value = new Set([...loadedRegionIds.value, id])
}

/** Drill into a region's subregions (UI-only; no API call). */
const openRegion = region => {
  const nextId = Number(region?.id)
  if (!nextId)
    return

  filters.name = ''
  expandedRegionId.value = nextId
  selectedSubregionId.value = null
  panelEntity.value = 'subregion'
  if (panelMode.value !== 'create')
    panelMode.value = 'dashboard'
  resetSubregionForm()
  loadedRegionIds.value = new Set([...loadedRegionIds.value, nextId])
}

const backToRegions = () => {
  filters.name = ''
  expandedRegionId.value = null
  selectedSubregionId.value = null
  editingSubregionId.value = null
  panelEntity.value = 'region'
  resetSubregionForm()
  if (panelMode.value === 'create')
    resetForm()
  panelMode.value = 'dashboard'
}

const selectSubregion = (row, region = null) => {
  if (region)
    expandedRegionId.value = region.id

  selectedSubregionId.value = row.id
  panelEntity.value = 'subregion'
  panelMode.value = 'detail'
}

const closeSubregionDetail = () => {
  selectedSubregionId.value = null
  panelEntity.value = 'subregion'
  panelMode.value = 'dashboard'
}

const openCreateSubregion = (region = null) => {
  if (region)
    expandedRegionId.value = region.id

  selectedSubregionId.value = null
  panelEntity.value = 'subregion'
  resetSubregionForm()
  panelMode.value = 'create'
}

const edit = region => {
  editingId.value = region.id
  Object.assign(form, {
    region_name: region.region_name || '',
    shortcode: region.shortcode || '',
    color: region.color || '',
  })
  panelEntity.value = 'region'
  panelMode.value = 'create'
}

const editSubregion = (row, region = null) => {
  if (region)
    expandedRegionId.value = region.id

  selectedSubregionId.value = row.id
  editingSubregionId.value = row.id
  Object.assign(subregionForm, {
    region_id: row.region_id || expandedRegionId.value || '',
    subregion_name: row.subregion_name || '',
    transient_occupancy_tax: row.transient_occupancy_tax ?? '',
    short_stay_len: row.short_stay_len ?? '',
    business_license_account: row.business_license_account === null || row.business_license_account === undefined || row.business_license_account === ''
      ? ''
      : Number(row.business_license_account),
    permit_no: row.permit_no || '',
    permit_issue_date: row.permit_issue_date || '',
    permit_length: row.permit_length || row.policy_len || '',
    permit_expiry_date: row.permit_expiry_date || '',
    limited_bookings: row.limited_bookings || (row.limit_type ? 'Yes' : 'No'),
    limit_type: row.limit_type || '',
    bookings_limit: row.bookings_limit || (row.limit_type === 'RESERVATIONS' ? row.limit_value : '') || '',
    nights_limit: row.nights_limit || (row.limit_type === 'NIGHTS' ? row.limit_value : '') || '',
    limit_filter: row.limit_filter || '',
  })
  panelEntity.value = 'subregion'
  panelMode.value = 'create'
}

const cancelCreate = () => {
  if (panelEntity.value === 'subregion') {
    resetSubregionForm()
    panelMode.value = selectedSubregionId.value != null ? 'detail' : 'dashboard'

    return
  }

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
    if (!regions.usingMocks)
      await regions.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

const buildSubregionPayload = () => {
  const limited = subregionForm.limited_bookings === 'Yes'
  const limitValue = subregionForm.limit_type === 'RESERVATIONS'
    ? subregionForm.bookings_limit
    : subregionForm.limit_type === 'NIGHTS'
      ? subregionForm.nights_limit
      : null

  return {
    region_id: subregionForm.region_id || expandedRegionId.value || null,
    subregion_name: subregionForm.subregion_name,
    transient_occupancy_tax: subregionForm.transient_occupancy_tax === '' ? null : subregionForm.transient_occupancy_tax,
    short_stay_len: subregionForm.short_stay_len === '' ? null : subregionForm.short_stay_len,
    business_license_account: subregionForm.business_license_account === '' ? null : subregionForm.business_license_account,
    permit_no: subregionForm.permit_no || null,
    permit_issue_date: subregionForm.permit_issue_date || null,
    permit_length: subregionForm.permit_length === '' ? null : subregionForm.permit_length,
    permit_expiry_date: subregionForm.permit_expiry_date || null,
    limited_bookings: subregionForm.limited_bookings,
    limit_type: limited ? (subregionForm.limit_type || null) : null,
    bookings_limit: limited && subregionForm.limit_type === 'RESERVATIONS' ? (subregionForm.bookings_limit || null) : null,
    nights_limit: limited && subregionForm.limit_type === 'NIGHTS' ? (subregionForm.nights_limit || null) : null,
    limit_value: limited ? (limitValue || null) : null,
    limit_filter: limited ? (subregionForm.limit_filter || null) : null,
    region: selectedRegion.value || null,
  }
}

const submitSubregion = async () => {
  try {
    const payload = buildSubregionPayload()
    const regionId = Number(payload.region_id)

    if (editingSubregionId.value)
      await subregions.update(editingSubregionId.value, payload)
    else
      await subregions.create(payload)

    const savedId = editingSubregionId.value || subregions.current?.id || null
    resetSubregionForm()
    if (regionId)
      expandedRegionId.value = regionId
    if (savedId) {
      selectedSubregionId.value = savedId
      panelMode.value = 'detail'
    }
    else {
      panelMode.value = 'dashboard'
    }

    if (!subregions.usingMocks)
      await loadSubregionsForRegion(regionId)
    else if (regionId)
      loadedRegionIds.value = new Set([...loadedRegionIds.value, regionId])

    if (!regions.usingMocks)
      await regions.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

const askDelete = (id, type = 'region') => {
  confirmDelete.open = true
  confirmDelete.id = id
  confirmDelete.type = type
}

const onDelete = async () => {
  try {
    if (confirmDelete.type === 'subregion') {
      await subregions.remove(confirmDelete.id)
      if (Number(selectedSubregionId.value) === Number(confirmDelete.id)) {
        selectedSubregionId.value = null
        panelMode.value = 'dashboard'
      }
      confirmDelete.open = false
      if (!subregions.usingMocks && expandedRegionId.value)
        await loadSubregionsForRegion(expandedRegionId.value)
      if (!regions.usingMocks)
        await regions.load()

      return
    }

    await regions.remove(confirmDelete.id)
    if (Number(expandedRegionId.value) === Number(confirmDelete.id)) {
      expandedRegionId.value = null
      selectedSubregionId.value = null
    }
    confirmDelete.open = false
    if (!regions.usingMocks)
      await regions.load()
  }
  catch {
    // Store exposes errors.
  }
}

onMounted(async () => {
  await Promise.all([
    regions.load(),
    subregions.load().catch(() => null),
  ])

  if (subregions.usingMocks || allSubregionRows.value.length) {
    loadedRegionIds.value = new Set(allRows.value.map(region => Number(region.id)))
  }

  const regionFromQuery = route.query.region_id
  if (regionFromQuery) {
    const region = allRows.value.find(row => Number(row.id) === Number(regionFromQuery))
    if (region)
      openRegion(region)
  }
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Regions & Subregions"
      subtitle="Manage property regions and municipality rules"
    >
      <template #actions>
        <BaseButton
          v-if="isSubregionView"
          color="primary"
          label="Add subregion"
          prepend-icon="bx-plus"
          @click="openCreateSubregion(selectedRegion)"
        />
        <BaseButton
          v-else
          color="primary"
          label="Create region"
          prepend-icon="bx-plus"
          @click="openPanel('create')"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="regions.usingMocks || subregions.usingMocks"
      type="info"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Showing UI preview data until the regions/subregions API is connected.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <template v-if="!isSubregionView">
          <VCard class="region-list-card">
            <VCardItem>
              <VCardTitle>{{ listTitle }}</VCardTitle>
              <template #append>
                <span class="text-caption text-medium-emphasis">
                  {{ listCountLabel }}
                </span>
              </template>
            </VCardItem>

            <VCardText class="pb-2">
              <BaseInput
                v-model="filters.name"
                placeholder="Search regions..."
                size="small"
                hide-details
                prepend-inner-icon="bx-search"
              />
            </VCardText>

            <VProgressLinear
              v-if="regions.loading"
              indeterminate
            />

            <div class="region-list">
              <EmptyState
                v-if="!regions.loading && rows.length === 0"
                title="No regions found"
                :description="allRows.length && rows.length === 0 ? 'No regions match the current filters.' : ''"
              />

              <div
                v-else
                class="entity-list"
              >
                <div
                  v-for="region in rows"
                  :key="region.id"
                  class="region-row region-row--clickable"
                  @click="openRegion(region)"
                >
                  <span
                    class="region-row__swatch"
                    :style="{ backgroundColor: region.color || 'rgba(75, 70, 92, 0.18)' }"
                  />

                  <div class="region-row__body">
                    <div class="region-row__name text-truncate">
                      {{ region.region_name }}
                    </div>
                    <div class="region-row__meta">
                      <span
                        v-if="region.shortcode"
                        class="region-row__chip"
                      >{{ region.shortcode }}</span>
                      <span class="region-row__chip region-row__chip--muted">
                        {{ subregionCount(region) }}
                        {{ subregionCount(region) === 1 ? 'subregion' : 'subregions' }}
                      </span>
                      <span
                        v-if="region.color"
                        class="region-row__chip region-row__chip--muted"
                      >{{ region.color }}</span>
                    </div>
                  </div>

                  <div
                    class="region-row__actions"
                    @click.stop
                  >
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
                      @click="askDelete(region.id, 'region')"
                    />
                  </div>

                  <VIcon
                    icon="bx-chevron-right"
                    size="22"
                    class="region-row__chevron text-medium-emphasis"
                  />
                </div>
              </div>
            </div>
          </VCard>
        </template>

        <template v-else>
          <VCard class="region-list-card">
            <VCardItem>
              <div class="list-card-heading">
                <BaseButton
                  size="small"
                  variant="tonal"
                  label="Back to regions"
                  prepend-icon="bx-arrow-back"
                  class="list-card-heading__back"
                  @click="backToRegions"
                />
                <VCardTitle>{{ listTitle }}</VCardTitle>
              </div>
              <template #append>
                <span class="text-caption text-medium-emphasis">
                  {{ listCountLabel }}
                </span>
              </template>
            </VCardItem>

            <VCardText class="pb-2">
              <BaseInput
                v-model="filters.name"
                placeholder="Search subregions..."
                size="small"
                hide-details
                prepend-inner-icon="bx-search"
              />
            </VCardText>

            <VProgressLinear
              v-if="subregions.loading"
              indeterminate
            />

            <div class="region-list">
              <EmptyState
                v-if="!subregions.loading && activeSubregionRows.length === 0"
                title="No subregions found"
                :description="subregionStats.total && activeSubregionRows.length === 0
                  ? 'No subregions match the current search.'
                  : 'No subregions yet. Add one to define Transient Occupancy Tax (TOT), permits, and booking limits.'"
              />

              <div
                v-else
                class="subregion-list"
              >
                <button
                  v-for="row in activeSubregionRows"
                  :key="row.id"
                  type="button"
                  class="subregion-row subregion-row--clickable"
                  :class="{ 'subregion-row--active': Number(selectedSubregionId) === Number(row.id) }"
                  @click="selectSubregion(row, selectedRegion)"
                >
                  <div class="subregion-row__main">
                    <div class="subregion-row__name text-truncate">
                      {{ row.subregion_name || row.name }}
                    </div>
                    <div class="subregion-row__meta">
                      <span class="subregion-row__stat">
                        <VIcon
                          icon="bx-percent"
                          size="14"
                        />
                        Transient Occupancy Tax {{ row.transient_occupancy_tax ?? '—' }}
                      </span>
                      <span class="subregion-row__stat">
                        <VIcon
                          icon="bx-moon"
                          size="14"
                        />
                        {{ row.short_stay_len ? `${row.short_stay_len} nights` : 'No short stay' }}
                      </span>
                      <span class="subregion-row__stat">
                        <VIcon
                          icon="bx-calendar-check"
                          size="14"
                        />
                        {{ limitLabel(row) }}
                      </span>
                      <VChip
                        v-if="licenseLabel(row)"
                        size="x-small"
                        :color="Number(row.business_license_account) === 1 ? 'success' : 'default'"
                        label
                      >
                        {{ licenseLabel(row) }}
                      </VChip>
                    </div>
                  </div>

                  <div
                    class="subregion-row__actions"
                    @click.stop
                  >
                    <BaseButton
                      size="small"
                      variant="tonal"
                      label="Edit"
                      class="me-2"
                      @click="editSubregion(row, selectedRegion)"
                    />
                    <BaseButton
                      size="small"
                      variant="tonal"
                      color="error"
                      label="Delete"
                      @click="askDelete(row.id, 'subregion')"
                    />
                  </div>
                </button>
              </div>
            </div>
          </VCard>
        </template>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardItem>
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template #append>
              <div class="d-flex flex-wrap gap-2 align-center">
                <BaseButton
                  v-if="!isSubregionView"
                  size="small"
                  :variant="panelMode === 'filter' && panelEntity === 'region' ? 'flat' : 'tonal'"
                  :color="panelMode === 'filter' && panelEntity === 'region' ? 'primary' : undefined"
                  label="Filter"
                  prepend-icon="bx-filter-alt"
                  @click="openPanel('filter')"
                />
                <BaseButton
                  v-if="!isSubregionView"
                  size="small"
                  :variant="panelMode === 'create' && panelEntity === 'region' ? 'flat' : 'tonal'"
                  :color="panelMode === 'create' && panelEntity === 'region' ? 'primary' : undefined"
                  label="Create"
                  prepend-icon="bx-plus"
                  @click="openPanel('create')"
                />
                <template v-if="panelMode === 'detail' && selectedSubregion">
                  <BaseButton
                    size="small"
                    color="primary"
                    label="Edit"
                    prepend-icon="bx-edit"
                    @click="editSubregion(selectedSubregion, selectedRegion)"
                  />
                  <VBtn
                    icon
                    variant="text"
                    size="small"
                    aria-label="Close subregion details"
                    @click="closeSubregionDetail"
                  >
                    <VIcon icon="bx-x" />
                  </VBtn>
                </template>
                <BaseButton
                  v-else-if="isSubregionView"
                  size="small"
                  :variant="panelMode === 'create' && panelEntity === 'subregion' ? 'flat' : 'tonal'"
                  :color="panelMode === 'create' && panelEntity === 'subregion' ? 'primary' : undefined"
                  label="Add subregion"
                  prepend-icon="bx-plus"
                  @click="openCreateSubregion(selectedRegion)"
                />
              </div>
            </template>
          </VCardItem>

          <VCardText>
            <template v-if="panelEntity === 'subregion' && panelMode === 'detail' && selectedSubregion">
              <div class="detail-hero mb-4">
                <div class="text-h6 text-truncate">
                  {{ selectedSubregion.subregion_name || selectedSubregion.name }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ selectedRegion?.region_name || 'Subregion' }}
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <VChip
                    v-if="licenseLabel(selectedSubregion)"
                    size="small"
                    :color="Number(selectedSubregion.business_license_account) === 1 ? 'success' : 'default'"
                    label
                  >
                    {{ licenseLabel(selectedSubregion) }}
                  </VChip>
                  <VChip
                    size="small"
                    :color="selectedSubregion.limited_bookings === 'Yes' || selectedSubregion.limit_type ? 'warning' : 'default'"
                    label
                  >
                    {{ limitLabel(selectedSubregion) }}
                  </VChip>
                </div>
              </div>

              <ul class="detail-list">
                <li>
                  <span class="detail-list__label">Transient Occupancy Tax</span>
                  <span class="detail-list__value">{{ selectedSubregion.transient_occupancy_tax ?? '—' }}%</span>
                </li>
                <li>
                  <span class="detail-list__label">Short-term nights</span>
                  <span class="detail-list__value">{{ selectedSubregion.short_stay_len ? `${selectedSubregion.short_stay_len} nights` : '—' }}</span>
                </li>
                <li>
                  <span class="detail-list__label">Permit number</span>
                  <span class="detail-list__value">{{ selectedSubregion.permit_no || '—' }}</span>
                </li>
                <li>
                  <span class="detail-list__label">Permit issue date</span>
                  <span class="detail-list__value">{{ selectedSubregion.permit_issue_date || '—' }}</span>
                </li>
                <li>
                  <span class="detail-list__label">Permit length</span>
                  <span class="detail-list__value">{{ selectedSubregion.permit_length || selectedSubregion.policy_len ? `${selectedSubregion.permit_length || selectedSubregion.policy_len} months` : '—' }}</span>
                </li>
                <li>
                  <span class="detail-list__label">Permit expiry</span>
                  <span class="detail-list__value">{{ selectedSubregion.permit_expiry_date || '—' }}</span>
                </li>
                <li>
                  <span class="detail-list__label">Booking limits</span>
                  <span class="detail-list__value">{{ limitLabel(selectedSubregion) }}</span>
                </li>
                <li>
                  <span class="detail-list__label">Limit based on</span>
                  <span class="detail-list__value">
                    {{ selectedSubregion.limit_filter === 'PERMIT_YEAR' ? 'Permit year' : selectedSubregion.limit_filter === 'CALENDAR' ? 'Calendar year' : '—' }}
                  </span>
                </li>
              </ul>
            </template>

            <template v-else-if="panelEntity === 'subregion' && panelMode === 'create'">
              <AppAlert :errors="subregions.errors" />
              <VForm
                class="entity-form"
                @submit.prevent="submitSubregion"
              >
                <BaseInput
                  v-model="subregionForm.subregion_name"
                  label="Subregion name"
                  class="mb-2"
                  :error="subregions.errors.subregion_name"
                />
                <BaseInput
                  v-model="subregionForm.transient_occupancy_tax"
                  label="Transient Occupancy Tax (%)"
                  placeholder="11.5"
                  class="mb-2"
                  :error="subregions.errors.transient_occupancy_tax"
                />
                <BaseSelect
                  v-model="subregionForm.short_stay_len"
                  label="Short-term nights (per stay)"
                  :items="shortStayItems"
                  clearable
                  class="mb-2"
                  :error="subregions.errors.short_stay_len"
                />
                <BaseSelect
                  v-model="subregionForm.business_license_account"
                  label="AC business license / permit"
                  :items="yesNoItems"
                  clearable
                  class="mb-2"
                  :error="subregions.errors.business_license_account"
                />
                <BaseInput
                  v-model="subregionForm.permit_no"
                  label="Permit number"
                  class="mb-2"
                  :error="subregions.errors.permit_no"
                />
                <BaseInput
                  v-model="subregionForm.permit_issue_date"
                  label="AC license / permit issue date"
                  type="date"
                  class="mb-2"
                  :error="subregions.errors.permit_issue_date"
                />
                <BaseSelect
                  v-model="subregionForm.permit_length"
                  label="AC license / permit length"
                  :items="permitLengthItems"
                  clearable
                  class="mb-2"
                  :error="subregions.errors.permit_length"
                />
                <BaseInput
                  v-model="subregionForm.permit_expiry_date"
                  label="AC license / permit expiry date"
                  type="date"
                  class="mb-2"
                  :error="subregions.errors.permit_expiry_date"
                />

                <VDivider class="my-4" />

                <BaseSelect
                  v-model="subregionForm.limited_bookings"
                  label="Limit short-term bookings?"
                  :items="limitedBookingsItems"
                  class="mb-2"
                  :error="subregions.errors.limited_bookings"
                />

                <template v-if="showLimitFields">
                  <BaseSelect
                    v-model="subregionForm.limit_type"
                    label="Reservation limit based on"
                    :items="limitTypeItems"
                    clearable
                    class="mb-2"
                    :error="subregions.errors.limit_type"
                  />
                  <BaseInput
                    v-if="showReservationLimit"
                    v-model="subregionForm.bookings_limit"
                    label="Number of reservations"
                    type="number"
                    class="mb-2"
                    :error="subregions.errors.bookings_limit"
                  />
                  <BaseInput
                    v-if="showNightsLimit"
                    v-model="subregionForm.nights_limit"
                    label="Number of nights"
                    type="number"
                    class="mb-2"
                    :error="subregions.errors.nights_limit"
                  />
                  <BaseSelect
                    v-model="subregionForm.limit_filter"
                    label="Limit based on"
                    :items="limitFilterItems"
                    clearable
                    class="mb-2"
                    :error="subregions.errors.limit_filter"
                  />
                </template>

                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="submit"
                    :label="editingSubregionId ? 'Update subregion' : 'Create subregion'"
                    :loading="subregions.loading"
                  />
                  <BaseButton
                    type="button"
                    variant="tonal"
                    label="Cancel"
                    @click="cancelCreate"
                  />
                </div>
              </VForm>
            </template>

            <template v-else-if="panelMode === 'filter'">
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
            </template>

            <template v-else-if="panelMode === 'create'">
              <AppAlert :errors="regions.errors" />
              <VForm
                class="entity-form"
                @submit.prevent="submit"
              >
                <BaseInput
                  v-model="form.region_name"
                  label="Region name"
                  class="mb-2"
                  :error="regions.errors.region_name"
                />
                <BaseInput
                  v-model="form.shortcode"
                  label="Shortcode"
                  class="mb-2"
                  :error="regions.errors.shortcode"
                />
                <BaseInput
                  v-model="form.color"
                  label="Color"
                  class="mb-2"
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
            </template>

            <template v-else>
              <template v-if="isSubregionView">
                <p class="text-body-2 text-medium-emphasis mb-4">
                  Subregion rules for {{ selectedRegion?.region_name }}
                </p>

                <VRow dense class="mb-2">
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Total subregions
                      </div>
                      <div class="text-h5">
                        {{ subregionStats.total }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        With Transient Occupancy Tax
                      </div>
                      <div class="text-h5">
                        {{ subregionStats.withTot }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Booking limits
                      </div>
                      <div class="text-h5">
                        {{ subregionStats.withLimits }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        AC license
                      </div>
                      <div class="text-h5">
                        {{ subregionStats.withLicense }}
                      </div>
                    </div>
                  </VCol>
                </VRow>
              </template>

              <template v-else>
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
              </template>
            </template>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <ConfirmDialog
      v-model="confirmDelete.open"
      :title="confirmDelete.type === 'subregion' ? 'Delete subregion?' : 'Delete region?'"
      :message="confirmDelete.type === 'subregion' ? 'This soft-deletes the subregion.' : 'This soft-deletes the region (deleted flag).'"
      confirm-label="Delete"
      confirm-color="error"
      :loading="confirmDelete.type === 'subregion' ? subregions.loading : regions.loading"
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

.region-list-card {
  overflow: hidden;
}

.region-list {
  padding: 0 12px 16px;
}

.list-card-heading {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  min-width: 0;
}

.list-card-heading__back {
  flex-shrink: 0;
}

.entity-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.region-row {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  min-width: 0;
  padding: 14px 16px;
  border: 1px solid rgba(75, 70, 92, 0.1);
  border-radius: 12px;
  background: rgba(var(--v-theme-surface), 1);
  box-shadow: 0 1px 2px rgba(75, 70, 92, 0.05);
  transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.region-row--clickable {
  cursor: pointer;
}

.region-row--clickable:hover {
  border-color: rgba(105, 108, 255, 0.28);
  background: rgba(105, 108, 255, 0.03);
  box-shadow: 0 2px 8px rgba(105, 108, 255, 0.08);
}

.region-row__swatch {
  flex-shrink: 0;
  width: 14px;
  height: 42px;
  border-radius: 999px;
  border: 1px solid rgba(75, 70, 92, 0.12);
}

.region-row__body {
  flex: 1;
  min-width: 0;
}

.region-row__name {
  font-weight: 600;
  font-size: 0.975rem;
  line-height: 1.3;
}

.region-row__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 6px;
}

.region-row__chip {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(105, 108, 255, 0.1);
  color: rgb(var(--v-theme-primary));
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.01em;
}

.region-row__chip--muted {
  background: rgba(75, 70, 92, 0.08);
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.region-row__actions {
  display: flex;
  flex-shrink: 0;
  align-items: center;
}

.region-row__chevron {
  flex-shrink: 0;
}

.region-color-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 2px;
  border: 1px solid rgba(75, 70, 92, 0.2);
}

.detail-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.detail-list li {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(75, 70, 92, 0.08);
}

.detail-list li:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.detail-list--compact li {
  padding: 7px 0;
}

.detail-list__label {
  flex-shrink: 0;
  color: rgba(var(--v-theme-on-surface), 0.55);
  font-size: 0.8125rem;
}

.detail-list__value {
  text-align: right;
  font-weight: 500;
  font-size: 0.875rem;
  word-break: break-word;
}

.detail-hero .text-h6 {
  line-height: 1.3;
}

.subregion-empty {
  padding: 16px;
  border: 1px dashed rgba(75, 70, 92, 0.18);
  border-radius: 10px;
  background: rgba(75, 70, 92, 0.03);
  color: rgba(var(--v-theme-on-surface), 0.65);
  font-size: 0.875rem;
}

.subregion-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.subregion-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  width: 100%;
  padding: 12px 14px;
  border: 1px solid rgba(75, 70, 92, 0.1);
  border-radius: 10px;
  background: rgba(var(--v-theme-surface), 1);
  text-align: left;
  transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.subregion-row--clickable {
  cursor: pointer;
}

.subregion-row:hover {
  border-color: rgba(105, 108, 255, 0.28);
  background: rgba(105, 108, 255, 0.03);
}

.subregion-row--active {
  border-color: rgba(105, 108, 255, 0.45);
  background: rgba(105, 108, 255, 0.08);
  box-shadow: 0 2px 8px rgba(105, 108, 255, 0.1);
}

.subregion-row__main {
  flex: 1;
  min-width: 0;
}

.subregion-row__name {
  font-weight: 600;
  font-size: 0.9rem;
}

.subregion-row__meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 12px;
  margin-top: 6px;
}

.subregion-row__stat {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-on-surface), 0.68);
}

.subregion-row__actions {
  display: flex;
  flex-shrink: 0;
  align-items: center;
}

@media (max-width: 960px) {
  .region-row,
  .subregion-row {
    flex-direction: column;
    align-items: stretch;
  }

  .region-row__actions,
  .subregion-row__actions {
    justify-content: flex-start;
  }

  .region-row__chevron {
    display: none;
  }

  .region-row__swatch {
    width: 42px;
    height: 8px;
  }
}
</style>
