<script setup>
const route = useRoute()
const subregions = useSubregionsStore()
const regions = useRegionsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const emptyForm = () => ({
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

const form = reactive(emptyForm())

const filters = reactive({
  name: '',
  region_id: '',
  limited_bookings: null,
  business_license_account: null,
})

const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = subregions.data?.data || subregions.data || []

  return Array.isArray(data) ? data : []
})

const regionList = computed(() => {
  const data = regions.data?.data || regions.data || []

  return Array.isArray(data) ? data : []
})

const regionOptions = computed(() => regionList.value
  .map(region => ({
    title: region.region_name || `Region #${region.id}`,
    value: region.id,
  }))
  .sort((a, b) => String(a.title).localeCompare(String(b.title))))

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

const limitedFilterItems = [
  { title: 'All subregions', value: null },
  { title: 'Limited bookings', value: 'Yes' },
  { title: 'No booking limit', value: 'No' },
]

const licenseFilterItems = [
  { title: 'All licenses', value: null },
  { title: 'AC license yes', value: 'yes' },
  { title: 'AC license no', value: 'no' },
]

const showLimitFields = computed(() => form.limited_bookings === 'Yes')
const showReservationLimit = computed(() => showLimitFields.value && form.limit_type === 'RESERVATIONS')
const showNightsLimit = computed(() => showLimitFields.value && form.limit_type === 'NIGHTS')

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()
  const regionId = filters.region_id === '' || filters.region_id === null
    ? null
    : Number(filters.region_id)

  return allRows.value.filter(row => {
    if (nameQuery) {
      const name = String(row.subregion_name || '').toLowerCase()
      if (!name.includes(nameQuery))
        return false
    }

    if (regionId !== null && Number(row.region_id) !== regionId)
      return false

    if (filters.limited_bookings === 'Yes' || filters.limited_bookings === 'No') {
      const limited = row.limited_bookings === 'Yes' || Boolean(row.limit_type)
      if (filters.limited_bookings === 'Yes' && !limited)
        return false
      if (filters.limited_bookings === 'No' && limited)
        return false
    }

    if (filters.business_license_account === 'yes' || filters.business_license_account === 'no') {
      const hasLicense = Number(row.business_license_account) === 1
      if (filters.business_license_account === 'yes' && !hasLicense)
        return false
      if (filters.business_license_account === 'no' && hasLicense)
        return false
    }

    return true
  })
})

const regionName = row => row.region?.region_name || regionList.value.find(region => Number(region.id) === Number(row.region_id))?.region_name || row.region_id || '—'

const stats = computed(() => {
  const list = allRows.value
  const withTax = list.filter(row => row.transient_occupancy_tax != null && row.transient_occupancy_tax !== '')
  const withLicense = list.filter(row => Number(row.business_license_account) === 1)
  const withLimits = list.filter(row => row.limited_bookings === 'Yes' || Boolean(row.limit_type))
  const withPermit = list.filter(row => Boolean(row.permit_no))

  return {
    total: list.length,
    withTax: withTax.length,
    withLicense: withLicense.length,
    withoutLicense: list.length - withLicense.length,
    withLimits: withLimits.length,
    withoutLimits: list.length - withLimits.length,
    withPermit: withPermit.length,
  }
})

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter subregions'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit subregion' : 'Create subregion'

  return 'Subregion overview'
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
    region_id: '',
    limited_bookings: null,
    business_license_account: null,
  })
}

const resetForm = () => {
  editingId.value = null
  Object.assign(form, emptyForm())
  if (filters.region_id)
    form.region_id = filters.region_id
}

const computeExpiryDate = () => {
  if (!form.permit_issue_date || !form.permit_length)
    return

  const issue = new Date(form.permit_issue_date)
  if (Number.isNaN(issue.getTime()))
    return

  const expiry = new Date(issue)
  expiry.setMonth(expiry.getMonth() + Number(form.permit_length))
  form.permit_expiry_date = expiry.toISOString().slice(0, 10)
}

watch(() => [form.permit_issue_date, form.permit_length], () => {
  computeExpiryDate()
})

watch(() => form.limited_bookings, value => {
  if (value !== 'Yes') {
    form.limit_type = ''
    form.bookings_limit = ''
    form.nights_limit = ''
    form.limit_filter = ''
  }
})

watch(() => form.limit_type, value => {
  if (value !== 'RESERVATIONS')
    form.bookings_limit = ''
  if (value !== 'NIGHTS')
    form.nights_limit = ''
})

const edit = row => {
  editingId.value = row.id
  Object.assign(form, {
    region_id: row.region_id || '',
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
  panelMode.value = 'create'
}

const cancelCreate = () => {
  resetForm()
  panelMode.value = 'dashboard'
}

const buildPayload = () => {
  const limited = form.limited_bookings === 'Yes'
  const limitValue = form.limit_type === 'RESERVATIONS'
    ? form.bookings_limit
    : form.limit_type === 'NIGHTS'
      ? form.nights_limit
      : null

  return {
    region_id: form.region_id || null,
    subregion_name: form.subregion_name,
    transient_occupancy_tax: form.transient_occupancy_tax === '' ? null : form.transient_occupancy_tax,
    short_stay_len: form.short_stay_len === '' ? null : form.short_stay_len,
    business_license_account: form.business_license_account === '' ? null : form.business_license_account,
    permit_no: form.permit_no || null,
    permit_issue_date: form.permit_issue_date || null,
    permit_length: form.permit_length === '' ? null : form.permit_length,
    permit_expiry_date: form.permit_expiry_date || null,
    limited_bookings: form.limited_bookings,
    limit_type: limited ? (form.limit_type || null) : null,
    bookings_limit: limited && form.limit_type === 'RESERVATIONS' ? (form.bookings_limit || null) : null,
    nights_limit: limited && form.limit_type === 'NIGHTS' ? (form.nights_limit || null) : null,
    limit_value: limited ? (limitValue || null) : null,
    limit_filter: limited ? (form.limit_filter || null) : null,
    region: regionList.value.find(region => Number(region.id) === Number(form.region_id)) || null,
  }
}

const submit = async () => {
  try {
    const payload = buildPayload()

    if (editingId.value)
      await subregions.update(editingId.value, payload)
    else
      await subregions.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
    if (!subregions.usingMocks)
      await subregions.load({ region_id: filters.region_id || undefined })
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
    await subregions.remove(confirmDelete.id)
    confirmDelete.open = false
    if (!subregions.usingMocks)
      await subregions.load({ region_id: filters.region_id || undefined })
  }
  catch {
    // Store exposes errors.
  }
}

const loadData = async () => {
  await Promise.all([
    regions.load().catch(() => null),
    subregions.load({ region_id: filters.region_id || undefined }),
  ])
}

onMounted(async () => {
  const regionFromQuery = route.query.region_id
  if (regionFromQuery)
    filters.region_id = Number(regionFromQuery) || regionFromQuery

  await loadData()
})

watch(() => filters.region_id, async () => {
  if (!subregions.usingMocks)
    await subregions.load({ region_id: filters.region_id || undefined })
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Subregions"
      subtitle="Municipality rules, TOT, permits, and booking limits"
    />

    <VAlert
      v-if="subregions.usingMocks || regions.usingMocks"
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
        <DataTableShell
          title="All subregions"
          :loading="subregions.loading"
          :empty="rows.length === 0"
          :empty-colspan="7"
          empty-title="No subregions found"
          :empty-description="allRows.length && rows.length === 0 ? 'No subregions match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Subregion</th>
                <th>Region</th>
                <th>TOT %</th>
                <th>Short stay</th>
                <th>AC license</th>
                <th>Limits</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="row in rows"
            :key="row.id"
          >
            <td>{{ row.subregion_name }}</td>
            <td>{{ regionName(row) }}</td>
            <td>{{ row.transient_occupancy_tax ?? '—' }}</td>
            <td>{{ row.short_stay_len ? `${row.short_stay_len} nights` : '—' }}</td>
            <td>{{ Number(row.business_license_account) === 1 ? 'Yes' : Number(row.business_license_account) === 0 ? 'No' : '—' }}</td>
            <td>
              <template v-if="row.limited_bookings === 'Yes' || row.limit_type">
                {{ row.limit_type === 'RESERVATIONS' ? 'Reservations' : row.limit_type === 'NIGHTS' ? 'Nights' : 'Yes' }}
                <span v-if="row.limit_value || row.bookings_limit || row.nights_limit">
                  ({{ row.limit_value || row.bookings_limit || row.nights_limit }})
                </span>
              </template>
              <template v-else>
                No
              </template>
            </td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(row)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(row.id)"
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
                Snapshot of municipality rules and permit coverage
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Booking limits
                    </div>
                    <div class="text-h5">
                      {{ stats.withLimits }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      No limit
                    </div>
                    <div class="text-h5">
                      {{ stats.withoutLimits }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      TOT set
                    </div>
                    <div class="text-h5">
                      {{ stats.withTax }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      AC license
                    </div>
                    <div class="text-h5">
                      {{ stats.withLicense }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          With permit number
                        </div>
                        <div class="text-h5">
                          {{ stats.withPermit }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-id-card"
                        size="28"
                        class="text-medium-emphasis"
                      />
                    </div>
                  </div>
                </VCol>
              </VRow>
            </div>

            <div v-else-if="panelMode === 'filter'">
              <VForm @submit.prevent>
                <BaseInput
                  v-model="filters.name"
                  label="Name contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.region_id"
                  label="Region"
                  :items="regionOptions"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.limited_bookings"
                  label="Booking limits"
                  :items="limitedFilterItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.business_license_account"
                  label="AC business license"
                  :items="licenseFilterItems"
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
              <AppAlert :errors="subregions.errors" />
              <VForm @submit.prevent="submit">
                <BaseSelect
                  v-model="form.region_id"
                  label="Region"
                  :items="regionOptions"
                  :error="subregions.errors.region_id"
                />
                <BaseInput
                  v-model="form.subregion_name"
                  label="Subregion name"
                  :error="subregions.errors.subregion_name"
                />
                <BaseInput
                  v-model="form.transient_occupancy_tax"
                  label="Transient occupancy tax (%)"
                  placeholder="11.5"
                  :error="subregions.errors.transient_occupancy_tax"
                />
                <BaseSelect
                  v-model="form.short_stay_len"
                  label="Short-term nights (per stay)"
                  :items="shortStayItems"
                  clearable
                  :error="subregions.errors.short_stay_len"
                />
                <BaseSelect
                  v-model="form.business_license_account"
                  label="AC business license / permit"
                  :items="yesNoItems"
                  clearable
                  :error="subregions.errors.business_license_account"
                />
                <BaseInput
                  v-model="form.permit_no"
                  label="Permit number"
                  :error="subregions.errors.permit_no"
                />
                <BaseInput
                  v-model="form.permit_issue_date"
                  label="AC license / permit issue date"
                  type="date"
                  :error="subregions.errors.permit_issue_date"
                />
                <BaseSelect
                  v-model="form.permit_length"
                  label="AC license / permit length"
                  :items="permitLengthItems"
                  clearable
                  :error="subregions.errors.permit_length"
                />
                <BaseInput
                  v-model="form.permit_expiry_date"
                  label="AC license / permit expiry date"
                  type="date"
                  :error="subregions.errors.permit_expiry_date"
                />

                <VDivider class="my-4" />

                <BaseSelect
                  v-model="form.limited_bookings"
                  label="Limit short-term bookings?"
                  :items="limitedBookingsItems"
                  :error="subregions.errors.limited_bookings"
                />

                <template v-if="showLimitFields">
                  <BaseSelect
                    v-model="form.limit_type"
                    label="Reservation limit based on"
                    :items="limitTypeItems"
                    clearable
                    :error="subregions.errors.limit_type"
                  />
                  <BaseInput
                    v-if="showReservationLimit"
                    v-model="form.bookings_limit"
                    label="Number of reservations"
                    type="number"
                    :error="subregions.errors.bookings_limit"
                  />
                  <BaseInput
                    v-if="showNightsLimit"
                    v-model="form.nights_limit"
                    label="Number of nights"
                    type="number"
                    :error="subregions.errors.nights_limit"
                  />
                  <BaseSelect
                    v-model="form.limit_filter"
                    label="Limit based on"
                    :items="limitFilterItems"
                    clearable
                    :error="subregions.errors.limit_filter"
                  />
                </template>

                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update subregion' : 'Create subregion'"
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
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete subregion?"
      message="This soft-deletes the subregion."
      confirm-label="Delete"
      confirm-color="error"
      :loading="subregions.loading"
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
