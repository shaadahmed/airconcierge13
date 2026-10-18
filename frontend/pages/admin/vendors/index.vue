<script setup>
const vendors = useVendorsStore()
const regions = useRegionsStore()
const subregions = useSubregionsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail' | 'create'>} */
const panelMode = ref('dashboard')

const hiredByItems = [
  { title: 'Air Concierge', value: '1' },
  { title: 'Hammer and Sponge', value: '2' },
  { title: 'Other', value: '3' },
]

const bookingFeeItems = [
  { title: 'Yes', value: 1 },
  { title: 'No', value: 0 },
]

const bookingFeePercentItems = Array.from({ length: 51 }, (_, i) => ({
  title: String(i),
  value: i,
}))

const positionItems = [
  { title: 'Cleaner', value: '1' },
  { title: 'Maintenance', value: '2' },
  { title: 'Other', value: '3' },
]

const statusItems = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
]

const yesNoItems = [
  { title: 'Yes', value: 1 },
  { title: 'No', value: 0 },
]

const form = reactive({
  region_id: '',
  subregion_id: '',
  full_name: '',
  company: '',
  email: '',
  phone: '',
  hired_by: '',
  other_hired_by: '',
  booking_fee: '',
  booking_fee_percentage: '',
  wnine: 1,
  position: '',
  other_position: '',
  standard_cleaning_fee: '',
  touch_up_cleaning_fee: '',
  cleaner_hourly_rate: '',
  status: 1,
  issuance_date: '',
  expiration_date: '',
})

const filters = reactive({
  search: '',
  region_id: '',
  hired_by: '',
  booking_fee: null,
  status: null,
  wnine: null,
  position: '',
})

const selectedId = ref(null)
const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = vendors.data?.data || vendors.data || []

  return Array.isArray(data) ? data : []
})

const displayName = vendor => vendor?.full_name || vendor?.name || '—'

const vendorRegion = vendor => vendor?.region?.name || (vendor?.region_id ? `Region #${vendor.region_id}` : 'No region')

const vendorCity = vendor => vendor?.subregion?.name || vendor?.city || (vendor?.subregion_id ? `City #${vendor.subregion_id}` : '—')

const hiredByLabel = value => {
  if (value == null || value === '')
    return '—'

  const known = hiredByItems.find(item => String(item.value) === String(value))
  if (known)
    return known.title

  return String(value)
}

const positionLabel = vendor => {
  const value = vendor?.position
  if (value == null || value === '')
    return '—'

  if (String(value) === '3' && vendor?.other_position)
    return vendor.other_position

  const known = positionItems.find(item => String(item.value) === String(value))
  if (known)
    return known.title

  return String(value)
}

const isVendorActive = vendor => {
  if (vendor?.status === false || vendor?.status === 0 || vendor?.status === '0')
    return false

  if (vendor?.active === false || vendor?.active === 0 || vendor?.active === '0')
    return false

  return true
}

const hasW9 = vendor => Boolean(vendor?.wnine === 1 || vendor?.wnine === true || vendor?.wnine === '1' || vendor?.w9_on_file)

const vendorInitials = vendor => {
  const name = displayName(vendor)
  if (!name || name === '—')
    return '?'

  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map(part => part[0]?.toUpperCase() || '')
    .join('')
}

const moneyLabel = value => {
  if (value === '' || value == null)
    return '—'

  const num = Number(value)

  return Number.isFinite(num) ? `$${num.toFixed(2)}` : String(value)
}

const rows = computed(() => {
  const searchQuery = filters.search.trim().toLowerCase()
  const regionId = filters.region_id === '' || filters.region_id === null
    ? null
    : Number(filters.region_id)
  const statusFilter = filters.status === '' || filters.status === null || filters.status === undefined
    ? null
    : Number(filters.status)
  const bookingFee = filters.booking_fee === '' || filters.booking_fee === null || filters.booking_fee === undefined
    ? null
    : Number(filters.booking_fee)

  return allRows.value.filter(vendor => {
    if (searchQuery) {
      const haystack = [
        displayName(vendor),
        vendor.company,
        vendor.email,
        vendor.phone,
        vendorRegion(vendor),
        vendorCity(vendor),
        hiredByLabel(vendor.hired_by),
        positionLabel(vendor),
      ].join(' ').toLowerCase()

      if (!haystack.includes(searchQuery))
        return false
    }

    if (regionId !== null && Number(vendor.region_id) !== regionId)
      return false

    if (filters.hired_by) {
      const hired = String(vendor.hired_by || '')
      if (filters.hired_by === '3') {
        if (hired === '1' || hired === '2')
          return false
      }
      else if (hired !== String(filters.hired_by)) {
        return false
      }
    }

    if (bookingFee !== null && Number(vendor.booking_fee) !== bookingFee)
      return false

    if (statusFilter !== null) {
      const active = isVendorActive(vendor)
      if (statusFilter === 1 && !active)
        return false
      if (statusFilter === 0 && active)
        return false
    }

    if (filters.wnine !== null && filters.wnine !== undefined && filters.wnine !== '') {
      const w9 = hasW9(vendor)
      if (Number(filters.wnine) === 1 && !w9)
        return false
      if (Number(filters.wnine) === 0 && w9)
        return false
    }

    if (filters.position && String(vendor.position) !== String(filters.position))
      return false

    return true
  })
})

const selectedVendor = computed(() => allRows.value.find(vendor => String(vendor.id) === String(selectedId.value)) || null)

const regionList = computed(() => {
  const data = regions.data?.data || regions.data || []

  return Array.isArray(data) ? data : []
})

const subregionList = computed(() => {
  const data = subregions.data?.data || subregions.data || []

  return Array.isArray(data) ? data : []
})

const regionOptions = computed(() => {
  const map = new Map()

  for (const region of regionList.value) {
    map.set(region.id, {
      title: region.name || `Region ${region.id}`,
      value: region.id,
    })
  }

  for (const vendor of allRows.value) {
    const id = vendor.region_id
    if (id == null || map.has(id))
      continue

    map.set(id, {
      title: vendor.region?.name || `Region ${id}`,
      value: id,
    })
  }

  return [...map.values()].sort((a, b) => String(a.title).localeCompare(String(b.title)))
})

const formSubregionOptions = computed(() => {
  const regionId = form.region_id === '' || form.region_id === null
    ? null
    : Number(form.region_id)

  return subregionList.value
    .filter(item => regionId === null || Number(item.region_id) === regionId)
    .map(item => ({
      title: item.name || `City ${item.id}`,
      value: item.id,
    }))
})

const showOtherHiredBy = computed(() => String(form.hired_by) === '3')
const showCleanerFees = computed(() => String(form.position) === '1')
const showOtherPosition = computed(() => String(form.position) === '3')

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(vendor => isVendorActive(vendor))
  const inactive = list.filter(vendor => !isVendorActive(vendor))
  const withW9 = list.filter(vendor => hasW9(vendor))
  const withoutW9 = list.filter(vendor => !hasW9(vendor))
  const withEmail = list.filter(vendor => Boolean(vendor.email))
  const cleaners = list.filter(vendor => String(vendor.position) === '1')

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    withW9: withW9.length,
    withoutW9: withoutW9.length,
    withEmail: withEmail.length,
    cleaners: cleaners.length,
  }
})

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter vendors'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit vendor' : 'Create vendor'
  if (panelMode.value === 'detail' && selectedVendor.value)
    return displayName(selectedVendor.value)

  return 'Vendor overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = selectedVendor.value ? 'detail' : 'dashboard'

    return
  }

  if (mode === 'create' && panelMode.value !== 'create')
    resetForm()

  panelMode.value = mode
}

const selectVendor = vendor => {
  selectedId.value = vendor.id
  panelMode.value = 'detail'
}

const clearFilters = () => {
  Object.assign(filters, {
    search: '',
    region_id: '',
    hired_by: '',
    booking_fee: null,
    status: null,
    wnine: null,
    position: '',
  })
}

onMounted(async () => {
  await Promise.all([
    vendors.load(),
    regions.load().catch(() => null),
    subregions.load().catch(() => null),
  ])
})

watch(() => form.region_id, () => {
  if (!form.subregion_id)
    return

  const stillValid = formSubregionOptions.value.some(item => Number(item.value) === Number(form.subregion_id))
  if (!stillValid)
    form.subregion_id = ''
})

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    region_id: '',
    subregion_id: '',
    full_name: '',
    company: '',
    email: '',
    phone: '',
    hired_by: '',
    other_hired_by: '',
    booking_fee: '',
    booking_fee_percentage: '',
    wnine: 1,
    position: '',
    other_position: '',
    standard_cleaning_fee: '',
    touch_up_cleaning_fee: '',
    cleaner_hourly_rate: '',
    status: 1,
    issuance_date: '',
    expiration_date: '',
  })
}

const normalizeHiredBy = vendor => {
  const value = vendor?.hired_by
  if (value == null || value === '')
    return ''

  if (String(value) === '1' || String(value) === '2')
    return String(value)

  return '3'
}

const edit = vendor => {
  selectedId.value = vendor.id
  editingId.value = vendor.id

  const hiredBy = normalizeHiredBy(vendor)

  Object.assign(form, {
    region_id: vendor.region_id || '',
    subregion_id: vendor.subregion_id || '',
    full_name: vendor.full_name || vendor.name || '',
    company: vendor.company || '',
    email: vendor.email || '',
    phone: vendor.phone || '',
    hired_by: hiredBy,
    other_hired_by: hiredBy === '3'
      ? (vendor.other_hired_by || (String(vendor.hired_by) !== '3' ? vendor.hired_by : '') || '')
      : '',
    booking_fee: vendor.booking_fee === '' || vendor.booking_fee == null ? '' : Number(vendor.booking_fee),
    booking_fee_percentage: vendor.booking_fee_percentage === '' || vendor.booking_fee_percentage == null
      ? ''
      : Number(vendor.booking_fee_percentage),
    wnine: hasW9(vendor) ? 1 : 0,
    position: vendor.position == null || vendor.position === '' ? '' : String(vendor.position),
    other_position: vendor.other_position || '',
    standard_cleaning_fee: vendor.standard_cleaning_fee ?? '',
    touch_up_cleaning_fee: vendor.touch_up_cleaning_fee ?? '',
    cleaner_hourly_rate: vendor.cleaner_hourly_rate ?? '',
    status: isVendorActive(vendor) ? 1 : 0,
    issuance_date: vendor.issuance_date || '',
    expiration_date: vendor.expiration_date || '',
  })
  panelMode.value = 'create'
}

const cancelCreate = () => {
  resetForm()
  panelMode.value = selectedVendor.value ? 'detail' : 'dashboard'
}

const submit = async () => {
  try {
    const region = regionList.value.find(item => Number(item.id) === Number(form.region_id))
    const subregion = subregionList.value.find(item => Number(item.id) === Number(form.subregion_id))

    const hiredByValue = String(form.hired_by) === '3'
      ? (form.other_hired_by || '3')
      : form.hired_by

    const payload = {
      region_id: form.region_id || null,
      subregion_id: form.subregion_id || null,
      full_name: form.full_name || null,
      company: form.company || null,
      email: form.email || null,
      phone: form.phone || null,
      hired_by: hiredByValue || null,
      other_hired_by: String(form.hired_by) === '3' ? (form.other_hired_by || null) : null,
      booking_fee: form.booking_fee === '' || form.booking_fee == null ? null : Number(form.booking_fee),
      booking_fee_percentage: form.booking_fee_percentage === '' || form.booking_fee_percentage == null
        ? null
        : Number(form.booking_fee_percentage),
      wnine: Number(form.wnine) === 1 ? 1 : 0,
      position: form.position || null,
      other_position: String(form.position) === '3' ? (form.other_position || null) : null,
      standard_cleaning_fee: String(form.position) === '1' && form.standard_cleaning_fee !== ''
        ? Number(form.standard_cleaning_fee)
        : null,
      touch_up_cleaning_fee: String(form.position) === '1' && form.touch_up_cleaning_fee !== ''
        ? Number(form.touch_up_cleaning_fee)
        : null,
      cleaner_hourly_rate: String(form.position) === '1' && form.cleaner_hourly_rate !== ''
        ? Number(form.cleaner_hourly_rate)
        : null,
      status: Number(form.status) === 1 ? 1 : 0,
      issuance_date: form.issuance_date || null,
      expiration_date: form.expiration_date || null,
    }

    if (region)
      payload.region = { id: region.id, name: region.name }

    if (subregion) {
      payload.subregion = {
        id: subregion.id,
        name: subregion.name,
      }
    }

    if (editingId.value)
      await vendors.update(editingId.value, payload)
    else
      await vendors.create(payload)

    const savedId = editingId.value || vendors.current?.id
    resetForm()
    if (savedId)
      selectedId.value = savedId
    panelMode.value = selectedId.value ? 'detail' : 'dashboard'
    if (!vendors.usingMocks)
      await vendors.load()
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
    const deletedId = confirmDelete.id
    await vendors.remove(deletedId)
    confirmDelete.open = false
    if (String(selectedId.value) === String(deletedId)) {
      selectedId.value = null
      panelMode.value = 'dashboard'
    }
    if (!vendors.usingMocks)
      await vendors.load()
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
      title="Vendors"
      subtitle="Manage cleaner / vendor staff"
    />

    <VAlert
      v-if="vendors.usingMocks"
      type="info"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Showing UI preview data until the vendors API is connected.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <VCard class="staff-list-card">
          <VCardItem>
            <VCardTitle>All vendors</VCardTitle>
          </VCardItem>

          <VCardText class="pb-2">
            <BaseInput
              v-model="filters.search"
              placeholder="Search vendors..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            v-if="vendors.loading"
            indeterminate
          />

          <div class="staff-list">
            <VRow dense>
              <VCol
                v-for="vendor in rows"
                :key="vendor.id"
                cols="12"
                lg="6"
              >
                <button
                  type="button"
                  class="staff-list-item"
                  :class="{ 'staff-list-item--selected': selectedId === vendor.id }"
                  @click="selectVendor(vendor)"
                >
                  <VAvatar
                    size="52"
                    rounded="lg"
                    class="staff-list-item__thumb"
                    color="primary"
                    variant="tonal"
                  >
                    <span class="staff-list-item__initials">{{ vendorInitials(vendor) }}</span>
                  </VAvatar>

                  <div class="staff-list-item__body">
                    <div class="staff-list-item__title text-truncate">
                      {{ displayName(vendor) }}
                    </div>
                    <div class="staff-list-item__subtitle text-truncate">
                      {{ vendorRegion(vendor) }}
                      <template v-if="vendorCity(vendor) !== '—'">
                        · {{ vendorCity(vendor) }}
                      </template>
                    </div>

                    <div class="staff-list-item__meta">
                      <span
                        v-if="vendor.company"
                        class="staff-list-item__meta-item"
                      >
                        <VIcon
                          icon="bx-buildings"
                          size="16"
                        />
                        <span class="text-truncate">{{ vendor.company }}</span>
                      </span>
                      <span class="staff-list-item__meta-item">
                        <VIcon
                          icon="bx-briefcase"
                          size="16"
                        />
                        <span class="text-truncate">{{ positionLabel(vendor) }}</span>
                      </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                      <VChip
                        size="x-small"
                        :color="isVendorActive(vendor) ? 'success' : 'secondary'"
                        label
                      >
                        {{ isVendorActive(vendor) ? 'Active' : 'Inactive' }}
                      </VChip>
                      <VChip
                        size="x-small"
                        :color="hasW9(vendor) ? 'success' : 'warning'"
                        label
                      >
                        {{ hasW9(vendor) ? 'W9 on file' : 'W9 missing' }}
                      </VChip>
                    </div>
                  </div>

                  <VIcon
                    icon="bx-chevron-right"
                    size="22"
                    class="staff-list-item__chevron"
                  />
                </button>
              </VCol>
            </VRow>

            <EmptyState
              v-if="!vendors.loading && rows.length === 0"
              title="No vendors found"
              :description="allRows.length && rows.length === 0 ? 'No vendors match the current filters.' : ''"
            />
          </div>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard class="staff-panel-card">
          <VCardItem>
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template #append>
              <div class="d-flex flex-wrap gap-2 align-center">
                <BaseButton
                  size="small"
                  color="primary-light"
                  variant="flat"
                  label="Filter"
                  prepend-icon="bx-filter-alt"
                  @click="openPanel('filter')"
                />
                <BaseButton
                  size="small"
                  color="primary"
                  label="Create"
                  prepend-icon="bx-plus"
                  @click="openPanel('create')"
                />
                <template v-if="panelMode === 'detail' && selectedVendor">
                  <BaseButton
                    size="small"
                    color="primary-light"
                    variant="flat"
                    label="Edit"
                    prepend-icon="bx-edit"
                    @click="edit(selectedVendor)"
                  />
                  <BaseButton
                    size="small"
                    label="Delete"
                    prepend-icon="bx-trash"
                    @click="askDelete(selectedVendor.id)"
                  />
                </template>
              </div>
            </template>
          </VCardItem>

          <VCardText class="staff-panel-body">
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of vendor records and W9 coverage
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Active
                    </div>
                    <div class="text-h5">
                      {{ stats.active }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Inactive
                    </div>
                    <div class="text-h5">
                      {{ stats.inactive }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      W9 on file
                    </div>
                    <div class="text-h5">
                      {{ stats.withW9 }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Cleaners
                    </div>
                    <div class="text-h5">
                      {{ stats.cleaners }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          Total vendors
                        </div>
                        <div class="text-h5">
                          {{ stats.total }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-user"
                        size="28"
                        class="text-medium-emphasis"
                      />
                    </div>
                  </div>
                </VCol>
              </VRow>
            </div>

            <div v-else-if="panelMode === 'detail' && selectedVendor">
              <div class="d-flex align-center gap-3 mb-3">
                <VAvatar
                  size="48"
                  rounded="lg"
                  color="primary"
                  variant="tonal"
                >
                  <span class="text-body-1 font-weight-medium">{{ vendorInitials(selectedVendor) }}</span>
                </VAvatar>
                <div class="min-w-0">
                  <div class="text-subtitle-1 font-weight-medium text-truncate">
                    {{ displayName(selectedVendor) }}
                  </div>
                  <div class="text-caption text-medium-emphasis text-truncate">
                    {{ vendorRegion(selectedVendor) }} · {{ vendorCity(selectedVendor) }}
                  </div>
                </div>
              </div>

              <ul class="staff-detail-list staff-detail-list--compact">
                <li>
                  <span class="staff-detail-list__label">Region</span>
                  <span class="staff-detail-list__value">{{ vendorRegion(selectedVendor) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">City</span>
                  <span class="staff-detail-list__value">{{ vendorCity(selectedVendor) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Company</span>
                  <span class="staff-detail-list__value">{{ selectedVendor.company || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Email</span>
                  <span class="staff-detail-list__value">{{ selectedVendor.email || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Phone</span>
                  <span class="staff-detail-list__value">{{ selectedVendor.phone || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Hired by</span>
                  <span class="staff-detail-list__value">{{ hiredByLabel(selectedVendor.hired_by) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Booking fee</span>
                  <span class="staff-detail-list__value">
                    {{ Number(selectedVendor.booking_fee) === 1 ? 'Yes' : Number(selectedVendor.booking_fee) === 0 ? 'No' : '—' }}
                  </span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Booking fee %</span>
                  <span class="staff-detail-list__value">
                    {{ selectedVendor.booking_fee_percentage == null || selectedVendor.booking_fee_percentage === '' ? '—' : `${selectedVendor.booking_fee_percentage}%` }}
                  </span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Position</span>
                  <span class="staff-detail-list__value">{{ positionLabel(selectedVendor) }}</span>
                </li>
                <li v-if="String(selectedVendor.position) === '1'">
                  <span class="staff-detail-list__label">Standard cleaning fee</span>
                  <span class="staff-detail-list__value">{{ moneyLabel(selectedVendor.standard_cleaning_fee) }}</span>
                </li>
                <li v-if="String(selectedVendor.position) === '1'">
                  <span class="staff-detail-list__label">Touch-up cleaning fee</span>
                  <span class="staff-detail-list__value">{{ moneyLabel(selectedVendor.touch_up_cleaning_fee) }}</span>
                </li>
                <li v-if="String(selectedVendor.position) === '1'">
                  <span class="staff-detail-list__label">Hourly rate</span>
                  <span class="staff-detail-list__value">{{ moneyLabel(selectedVendor.cleaner_hourly_rate) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">W9</span>
                  <span class="staff-detail-list__value">{{ hasW9(selectedVendor) ? 'Yes' : 'No' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Status</span>
                  <span class="staff-detail-list__value">{{ isVendorActive(selectedVendor) ? 'Active' : 'Inactive' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Insurance issued</span>
                  <span class="staff-detail-list__value">{{ selectedVendor.issuance_date || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Insurance expires</span>
                  <span class="staff-detail-list__value">{{ selectedVendor.expiration_date || '—' }}</span>
                </li>
              </ul>
            </div>

            <div v-else-if="panelMode === 'filter'">
              <VForm
                class="staff-filter-form"
                @submit.prevent
              >
                <BaseSelect
                  v-model="filters.region_id"
                  label="Region"
                  :items="regionOptions"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.hired_by"
                  label="Hired by"
                  :items="hiredByItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.booking_fee"
                  label="Booking fee"
                  :items="bookingFeeItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.status"
                  label="Status"
                  :items="statusItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.wnine"
                  label="W9"
                  :items="yesNoItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.position"
                  label="Vendor position"
                  :items="positionItems"
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
                    label="Back"
                    @click="panelMode = selectedVendor ? 'detail' : 'dashboard'"
                  />
                </div>
              </VForm>
            </div>

            <div v-else-if="panelMode === 'create'">
              <AppAlert :errors="vendors.errors" />
              <VForm
                class="staff-form"
                @submit.prevent="submit"
              >
                <BaseSelect
                  v-model="form.region_id"
                  label="Region"
                  :items="regionOptions"
                  clearable
                  :error="vendors.errors.region_id"
                />
                <BaseSelect
                  v-model="form.subregion_id"
                  label="City"
                  :items="formSubregionOptions"
                  clearable
                  :error="vendors.errors.subregion_id"
                />
                <BaseInput
                  v-model="form.full_name"
                  label="Full name"
                  :error="vendors.errors.full_name"
                />
                <BaseInput
                  v-model="form.company"
                  label="Company"
                  :error="vendors.errors.company"
                />
                <BaseInput
                  v-model="form.email"
                  label="Email"
                  type="email"
                  :error="vendors.errors.email"
                />
                <BaseInput
                  v-model="form.phone"
                  label="Phone"
                  :error="vendors.errors.phone"
                />
                <BaseSelect
                  v-model="form.hired_by"
                  label="Hired by"
                  :items="hiredByItems"
                  clearable
                  :error="vendors.errors.hired_by"
                />
                <BaseInput
                  v-if="showOtherHiredBy"
                  v-model="form.other_hired_by"
                  label="Other hired by"
                  :error="vendors.errors.other_hired_by"
                />
                <BaseSelect
                  v-model="form.booking_fee"
                  label="Booking fee"
                  :items="bookingFeeItems"
                  clearable
                  :error="vendors.errors.booking_fee"
                />
                <BaseSelect
                  v-model="form.booking_fee_percentage"
                  label="Booking fee % amount"
                  :items="bookingFeePercentItems"
                  clearable
                  :error="vendors.errors.booking_fee_percentage"
                />
                <BaseSelect
                  v-model="form.wnine"
                  label="W9"
                  :items="yesNoItems"
                  :error="vendors.errors.wnine"
                />
                <BaseSelect
                  v-model="form.position"
                  label="Position"
                  :items="positionItems"
                  clearable
                  :error="vendors.errors.position"
                />
                <template v-if="showCleanerFees">
                  <BaseInput
                    v-model="form.standard_cleaning_fee"
                    label="Standard cleaning fee"
                    type="number"
                    :error="vendors.errors.standard_cleaning_fee"
                  />
                  <BaseInput
                    v-model="form.touch_up_cleaning_fee"
                    label="Touch-up cleaning fee"
                    type="number"
                    :error="vendors.errors.touch_up_cleaning_fee"
                  />
                  <BaseInput
                    v-model="form.cleaner_hourly_rate"
                    label="Hourly rate for additional work"
                    type="number"
                    :error="vendors.errors.cleaner_hourly_rate"
                  />
                </template>
                <BaseInput
                  v-if="showOtherPosition"
                  v-model="form.other_position"
                  label="Other position"
                  :error="vendors.errors.other_position"
                />
                <BaseSelect
                  v-model="form.status"
                  label="Status"
                  :items="statusItems"
                  :error="vendors.errors.status"
                />
                <BaseInput
                  v-model="form.issuance_date"
                  label="Insurance date of issuance"
                  type="date"
                  :error="vendors.errors.issuance_date"
                />
                <BaseInput
                  v-model="form.expiration_date"
                  label="Insurance date of expiration"
                  type="date"
                  :error="vendors.errors.expiration_date"
                />
                <div class="d-flex flex-wrap gap-2 staff-form__actions">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update vendor' : 'Create vendor'"
                    :loading="vendors.loading"
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
      title="Delete vendor?"
      message="This soft-deletes the vendor (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="vendors.loading"
      @confirm="onDelete"
    />
  </div>
</template>

<style scoped>
.staff-list-card,
.staff-panel-card {
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 160px);
}

.staff-panel-body {
  overflow-y: auto;
  /* Extra room so the first outlined label is not clipped by overflow. */
  padding-block-start: 1.25rem;
}

.staff-filter-form {
  padding-top: 0.25rem;
}

.staff-list {
  overflow-y: auto;
  padding: 0 12px 12px;
}

.staff-list :deep(.v-col) {
  display: flex;
}

.staff-list-item {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  height: 100%;
  min-height: 112px;
  padding: 14px 16px;
  border: 1px solid rgba(75, 70, 92, 0.1);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 1px 2px rgba(75, 70, 92, 0.06);
  text-align: left;
  cursor: pointer;
  transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.staff-list-item:hover {
  background: rgba(104, 101, 255, 0.04);
  border-color: rgba(104, 101, 255, 0.28);
}

.staff-list-item--selected {
  background: rgba(104, 101, 255, 0.1);
  border-color: rgba(104, 101, 255, 0.45);
  box-shadow: 0 2px 8px rgba(104, 101, 255, 0.12);
}

.staff-list-item__thumb {
  flex-shrink: 0;
}

.staff-list-item__initials {
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.02em;
}

.staff-list-item__body {
  flex: 1;
  min-width: 0;
}

.staff-list-item__title {
  font-weight: 600;
  font-size: 0.975rem;
  line-height: 1.3;
  color: rgb(var(--v-theme-on-surface));
}

.staff-list-item__subtitle {
  margin-top: 2px;
  font-size: 0.8125rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.staff-list-item__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 14px;
  margin-top: 8px;
}

.staff-list-item__meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  max-width: 100%;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.staff-list-item__chevron {
  flex-shrink: 0;
  color: rgba(var(--v-theme-on-surface), 0.35);
}

.staff-list-item--selected .staff-list-item__chevron {
  color: rgb(var(--v-theme-primary));
}

.staff-form :deep(.v-input),
.staff-form :deep(.v-selection-control) {
  margin-bottom: 1rem;
}

.staff-form__actions {
  margin-top: 0.25rem;
}

.staff-detail-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.staff-detail-list li {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(75, 70, 92, 0.08);
}

.staff-detail-list li:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.staff-detail-list--compact li {
  padding: 7px 0;
}

.staff-detail-list__label {
  flex-shrink: 0;
  max-width: 45%;
  font-size: 0.8125rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.staff-detail-list__value {
  text-align: right;
  font-weight: 500;
  font-size: 0.875rem;
  color: rgb(var(--v-theme-on-surface));
  word-break: break-word;
}

.entity-stat-tile {
  border: 1px solid rgba(75, 70, 92, 0.08);
  background: rgba(75, 70, 92, 0.03);
}

.entity-stat-tile--success {
  background: rgba(22, 163, 74, 0.08);
}

.entity-stat-tile--muted {
  background: rgba(168, 170, 174, 0.12);
}

.entity-stat-tile--primary {
  background: rgba(104, 101, 255, 0.1);
}

.entity-stat-tile--info {
  background: rgba(104, 101, 255, 0.1);
}

.entity-stat-tile--warning {
  background: rgba(217, 119, 6, 0.1);
}
</style>
