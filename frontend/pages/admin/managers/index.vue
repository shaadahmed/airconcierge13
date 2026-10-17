<script setup>
const managers = useManagersStore()
const regions = useRegionsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail' | 'create'>} */
const panelMode = ref('dashboard')

const salaryTypeItems = [
  { title: 'Salary', value: 0 },
  { title: 'Salary + Commissions', value: 1 },
  { title: 'Commission Only', value: 2 },
]

const employmentStatusItems = [
  { title: 'Employee', value: 0 },
  { title: 'Contractor', value: 1 },
]

const statusItems = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
]

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  salary_type: '',
  employment_status: '',
  is_active: 1,
  compensation_structure: '',
  home_address: '',
  city: '',
  state: '',
  zip: '',
  region_ids: [],
})

const filters = reactive({
  search: '',
  region_id: '',
  status: null,
  salary_type: null,
  employment_status: null,
  home_address: '',
})

const selectedId = ref(null)
const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = managers.data?.data || managers.data || []

  return Array.isArray(data) ? data : []
})

const displayName = manager => {
  const combined = [manager?.first_name, manager?.last_name].filter(Boolean).join(' ')

  return combined || '—'
}

const isManagerActive = manager => {
  if (manager?.is_active === false || manager?.is_active === 0 || manager?.is_active === '0')
    return false

  return true
}

const salaryTypeLabel = value => {
  if (value === '' || value == null)
    return '—'

  const match = salaryTypeItems.find(item => Number(item.value) === Number(value))

  return match?.title || String(value)
}

const employmentStatusLabel = value => {
  if (value === '' || value == null)
    return '—'

  const match = employmentStatusItems.find(item => Number(item.value) === Number(value))

  return match?.title || String(value)
}

const managerRegions = manager => {
  if (Array.isArray(manager?.regions) && manager.regions.length)
    return manager.regions

  if (manager?.region)
    return [manager.region]

  return []
}

const regionsLabel = manager => {
  const list = managerRegions(manager)
  if (!list.length)
    return 'No regions'

  return list.map(region => region.region_name || region.name || `Region #${region.id}`).join(', ')
}

const managerInitials = manager => {
  const name = displayName(manager)
  if (!name || name === '—')
    return '?'

  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map(part => part[0]?.toUpperCase() || '')
    .join('')
}

const rows = computed(() => {
  const searchQuery = filters.search.trim().toLowerCase()
  const regionId = filters.region_id === '' || filters.region_id === null
    ? null
    : Number(filters.region_id)
  const statusFilter = filters.status === '' || filters.status === null || filters.status === undefined
    ? null
    : Number(filters.status)
  const salaryType = filters.salary_type === '' || filters.salary_type === null || filters.salary_type === undefined
    ? null
    : Number(filters.salary_type)
  const employmentStatus = filters.employment_status === '' || filters.employment_status === null || filters.employment_status === undefined
    ? null
    : Number(filters.employment_status)
  const addressQuery = filters.home_address.trim().toLowerCase()

  return allRows.value.filter(manager => {
    if (searchQuery) {
      const haystack = [
        displayName(manager),
        manager.email,
        manager.phone,
        manager.city,
        manager.state,
        manager.home_address,
        regionsLabel(manager),
        salaryTypeLabel(manager.salary_type),
        employmentStatusLabel(manager.employment_status),
      ].join(' ').toLowerCase()

      if (!haystack.includes(searchQuery))
        return false
    }

    if (regionId !== null) {
      const regionIds = managerRegions(manager).map(region => Number(region.id))
      if (!regionIds.includes(regionId) && Number(manager.region_id) !== regionId)
        return false
    }

    if (statusFilter !== null) {
      const active = isManagerActive(manager)
      if (statusFilter === 1 && !active)
        return false
      if (statusFilter === 0 && active)
        return false
    }

    if (salaryType !== null && Number(manager.salary_type) !== salaryType)
      return false

    if (employmentStatus !== null && Number(manager.employment_status) !== employmentStatus)
      return false

    if (addressQuery) {
      const address = String(manager.home_address || '').toLowerCase()
      if (!address.includes(addressQuery))
        return false
    }

    return true
  })
})

const selectedManager = computed(() => allRows.value.find(manager => String(manager.id) === String(selectedId.value)) || null)

const regionList = computed(() => {
  const data = regions.data?.data || regions.data || []

  return Array.isArray(data) ? data : []
})

const regionOptions = computed(() => {
  const map = new Map()

  for (const region of regionList.value) {
    map.set(region.id, {
      title: region.region_name || `Region ${region.id}`,
      value: region.id,
    })
  }

  for (const manager of allRows.value) {
    for (const region of managerRegions(manager)) {
      if (region?.id == null || map.has(region.id))
        continue

      map.set(region.id, {
        title: region.region_name || region.name || `Region ${region.id}`,
        value: region.id,
      })
    }
  }

  return [...map.values()].sort((a, b) => String(a.title).localeCompare(String(b.title)))
})

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(manager => isManagerActive(manager))
  const inactive = list.filter(manager => !isManagerActive(manager))
  const withEmail = list.filter(manager => Boolean(manager.email))
  const withPhone = list.filter(manager => Boolean(manager.phone))
  const withRegions = list.filter(manager => managerRegions(manager).length > 0)
  const contractors = list.filter(manager => Number(manager.employment_status) === 1)

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    withEmail: withEmail.length,
    withPhone: withPhone.length,
    withRegions: withRegions.length,
    contractors: contractors.length,
  }
})

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter managers'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit manager' : 'Create manager'
  if (panelMode.value === 'detail' && selectedManager.value)
    return displayName(selectedManager.value)

  return 'Manager overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = selectedManager.value ? 'detail' : 'dashboard'

    return
  }

  if (mode === 'create' && panelMode.value !== 'create')
    resetForm()

  panelMode.value = mode
}

const selectManager = manager => {
  selectedId.value = manager.id
  panelMode.value = 'detail'
}

const clearFilters = () => {
  Object.assign(filters, {
    search: '',
    region_id: '',
    status: null,
    salary_type: null,
    employment_status: null,
    home_address: '',
  })
}

onMounted(async () => {
  await Promise.all([
    managers.load(),
    regions.load().catch(() => null),
  ])
})

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    salary_type: '',
    employment_status: '',
    is_active: 1,
    compensation_structure: '',
    home_address: '',
    city: '',
    state: '',
    zip: '',
    region_ids: [],
  })
}

const edit = manager => {
  selectedId.value = manager.id
  editingId.value = manager.id
  Object.assign(form, {
    first_name: manager.first_name || '',
    last_name: manager.last_name || '',
    email: manager.email || '',
    phone: manager.phone || '',
    salary_type: manager.salary_type === '' || manager.salary_type == null ? '' : Number(manager.salary_type),
    employment_status: manager.employment_status === '' || manager.employment_status == null
      ? ''
      : Number(manager.employment_status),
    is_active: isManagerActive(manager) ? 1 : 0,
    compensation_structure: manager.compensation_structure || '',
    home_address: manager.home_address || '',
    city: manager.city || '',
    state: manager.state || '',
    zip: manager.zip || '',
    region_ids: managerRegions(manager).map(region => region.id),
  })
  panelMode.value = 'create'
}

const cancelCreate = () => {
  resetForm()
  panelMode.value = selectedManager.value ? 'detail' : 'dashboard'
}

const submit = async () => {
  try {
    const selectedRegions = regionList.value
      .filter(region => form.region_ids.map(Number).includes(Number(region.id)))
      .map(region => ({ id: region.id, region_name: region.region_name }))

    const payload = {
      first_name: form.first_name || null,
      last_name: form.last_name || null,
      email: form.email || null,
      phone: form.phone || null,
      salary_type: form.salary_type === '' || form.salary_type == null ? null : Number(form.salary_type),
      employment_status: form.employment_status === '' || form.employment_status == null
        ? null
        : Number(form.employment_status),
      is_active: Number(form.is_active) === 1 ? 1 : 0,
      compensation_structure: form.compensation_structure || null,
      home_address: form.home_address || null,
      city: form.city || null,
      state: form.state || null,
      zip: form.zip || null,
      region_ids: form.region_ids || [],
      regions: selectedRegions,
    }

    if (editingId.value)
      await managers.update(editingId.value, payload)
    else
      await managers.create(payload)

    const savedId = editingId.value || managers.current?.id
    resetForm()
    if (savedId)
      selectedId.value = savedId
    panelMode.value = selectedId.value ? 'detail' : 'dashboard'
    if (!managers.usingMocks)
      await managers.load()
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
    await managers.remove(deletedId)
    confirmDelete.open = false
    if (String(selectedId.value) === String(deletedId)) {
      selectedId.value = null
      panelMode.value = 'dashboard'
    }
    if (!managers.usingMocks)
      await managers.load()
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
      title="Regional Managers"
      subtitle="Manage regional managers"
    />

    <VAlert
      v-if="managers.usingMocks"
      type="info"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Showing UI preview data until the managers API is connected.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <VCard class="staff-list-card">
          <VCardItem>
            <VCardTitle>All managers</VCardTitle>
          </VCardItem>

          <VCardText class="pb-2">
            <BaseInput
              v-model="filters.search"
              placeholder="Search managers..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            v-if="managers.loading"
            indeterminate
          />

          <div class="staff-list">
            <VRow dense>
              <VCol
                v-for="manager in rows"
                :key="manager.id"
                cols="12"
                lg="6"
              >
                <button
                  type="button"
                  class="staff-list-item"
                  :class="{ 'staff-list-item--selected': selectedId === manager.id }"
                  @click="selectManager(manager)"
                >
                  <VAvatar
                    size="52"
                    rounded="lg"
                    class="staff-list-item__thumb"
                    color="primary"
                    variant="tonal"
                  >
                    <span class="staff-list-item__initials">{{ managerInitials(manager) }}</span>
                  </VAvatar>

                  <div class="staff-list-item__body">
                    <div class="staff-list-item__title text-truncate">
                      {{ displayName(manager) }}
                    </div>
                    <div class="staff-list-item__subtitle text-truncate">
                      {{ regionsLabel(manager) }}
                      <template v-if="manager.email">
                        · {{ manager.email }}
                      </template>
                    </div>

                    <div class="staff-list-item__meta">
                      <span
                        v-if="manager.phone"
                        class="staff-list-item__meta-item"
                      >
                        <VIcon
                          icon="bx-phone"
                          size="16"
                        />
                        {{ manager.phone }}
                      </span>
                      <span class="staff-list-item__meta-item">
                        <VIcon
                          icon="bx-briefcase"
                          size="16"
                        />
                        <span class="text-truncate">{{ employmentStatusLabel(manager.employment_status) }}</span>
                      </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                      <VChip
                        size="x-small"
                        :color="isManagerActive(manager) ? 'success' : 'secondary'"
                        label
                      >
                        {{ isManagerActive(manager) ? 'Active' : 'Inactive' }}
                      </VChip>
                      <VChip
                        size="x-small"
                        color="info"
                        label
                        variant="tonal"
                      >
                        {{ salaryTypeLabel(manager.salary_type) }}
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
              v-if="!managers.loading && rows.length === 0"
              title="No managers found"
              :description="allRows.length && rows.length === 0 ? 'No managers match the current filters.' : ''"
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
                <template v-if="panelMode === 'detail' && selectedManager">
                  <BaseButton
                    size="small"
                    color="primary-light"
                    variant="flat"
                    label="Edit"
                    prepend-icon="bx-edit"
                    @click="edit(selectedManager)"
                  />
                  <BaseButton
                    size="small"
                    label="Delete"
                    prepend-icon="bx-trash"
                    @click="askDelete(selectedManager.id)"
                  />
                </template>
              </div>
            </template>
          </VCardItem>

          <VCardText class="staff-panel-body">
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of manager records and coverage
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
                      With regions
                    </div>
                    <div class="text-h5">
                      {{ stats.withRegions }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Contractors
                    </div>
                    <div class="text-h5">
                      {{ stats.contractors }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          With phone
                        </div>
                        <div class="text-h5">
                          {{ stats.withPhone }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-phone"
                        size="28"
                        class="text-medium-emphasis"
                      />
                    </div>
                  </div>
                </VCol>
              </VRow>
            </div>

            <div v-else-if="panelMode === 'detail' && selectedManager">
              <div class="d-flex align-center gap-3 mb-3">
                <VAvatar
                  size="48"
                  rounded="lg"
                  color="primary"
                  variant="tonal"
                >
                  <span class="text-body-1 font-weight-medium">{{ managerInitials(selectedManager) }}</span>
                </VAvatar>
                <div class="min-w-0">
                  <div class="text-subtitle-1 font-weight-medium text-truncate">
                    {{ displayName(selectedManager) }}
                  </div>
                  <div class="text-caption text-medium-emphasis text-truncate">
                    {{ regionsLabel(selectedManager) }}
                  </div>
                </div>
              </div>

              <ul class="staff-detail-list staff-detail-list--compact">
                <li>
                  <span class="staff-detail-list__label">First name</span>
                  <span class="staff-detail-list__value">{{ selectedManager.first_name || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Last name</span>
                  <span class="staff-detail-list__value">{{ selectedManager.last_name || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Email</span>
                  <span class="staff-detail-list__value">{{ selectedManager.email || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Phone</span>
                  <span class="staff-detail-list__value">{{ selectedManager.phone || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Status</span>
                  <span class="staff-detail-list__value">{{ isManagerActive(selectedManager) ? 'Active' : 'Inactive' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Employment status</span>
                  <span class="staff-detail-list__value">{{ employmentStatusLabel(selectedManager.employment_status) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Compensation structure</span>
                  <span class="staff-detail-list__value">{{ salaryTypeLabel(selectedManager.salary_type) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Compensation details</span>
                  <span class="staff-detail-list__value">{{ selectedManager.compensation_structure || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Regions</span>
                  <span class="staff-detail-list__value">{{ regionsLabel(selectedManager) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Home address</span>
                  <span class="staff-detail-list__value">{{ selectedManager.home_address || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">City</span>
                  <span class="staff-detail-list__value">{{ selectedManager.city || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">State</span>
                  <span class="staff-detail-list__value">{{ selectedManager.state || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Zip</span>
                  <span class="staff-detail-list__value">{{ selectedManager.zip || '—' }}</span>
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
                  label="City / Region"
                  :items="regionOptions"
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
                  v-model="filters.salary_type"
                  label="Compensation structure"
                  :items="salaryTypeItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.employment_status"
                  label="Employment status"
                  :items="employmentStatusItems"
                  clearable
                  class="mb-2"
                />
                <BaseInput
                  v-model="filters.home_address"
                  label="Home address"
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
                    @click="panelMode = selectedManager ? 'detail' : 'dashboard'"
                  />
                </div>
              </VForm>
            </div>

            <div v-else-if="panelMode === 'create'">
              <AppAlert :errors="managers.errors" />
              <VForm
                class="staff-form"
                @submit.prevent="submit"
              >
                <BaseInput
                  v-model="form.first_name"
                  label="First name"
                  :error="managers.errors.first_name"
                />
                <BaseInput
                  v-model="form.last_name"
                  label="Last name"
                  :error="managers.errors.last_name"
                />
                <BaseInput
                  v-model="form.email"
                  label="Email"
                  type="email"
                  :error="managers.errors.email"
                />
                <BaseInput
                  v-model="form.phone"
                  label="Phone"
                  :error="managers.errors.phone"
                />
                <BaseSelect
                  v-model="form.salary_type"
                  label="Compensation structure"
                  :items="salaryTypeItems"
                  clearable
                  :error="managers.errors.salary_type"
                />
                <BaseTextarea
                  v-model="form.compensation_structure"
                  label="Compensation structure details"
                  :error="managers.errors.compensation_structure"
                />
                <BaseSelect
                  v-model="form.employment_status"
                  label="Employment status"
                  :items="employmentStatusItems"
                  clearable
                  :error="managers.errors.employment_status"
                />
                <BaseSelect
                  v-model="form.is_active"
                  label="Status"
                  :items="statusItems"
                  :error="managers.errors.is_active"
                />
                <BaseSelect
                  v-model="form.region_ids"
                  label="Regions"
                  :items="regionOptions"
                  multiple
                  chips
                  clearable
                  :error="managers.errors.region_ids"
                />
                <BaseInput
                  v-model="form.home_address"
                  label="Home address"
                  :error="managers.errors.home_address"
                />
                <BaseInput
                  v-model="form.city"
                  label="City"
                  :error="managers.errors.city"
                />
                <BaseInput
                  v-model="form.state"
                  label="State"
                  :error="managers.errors.state"
                />
                <BaseInput
                  v-model="form.zip"
                  label="Zip"
                  :error="managers.errors.zip"
                />
                <div class="d-flex flex-wrap gap-2 staff-form__actions">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update manager' : 'Create manager'"
                    :loading="managers.loading"
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
      title="Delete manager?"
      message="This soft-deletes the manager (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="managers.loading"
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
