<script setup>
const owners = useOwnersStore()
const regions = useRegionsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail' | 'create'>} */
const panelMode = ref('dashboard')

const paymentMethodItems = [
  { title: 'Another owner is receiving under an above method', value: 'Another owner is receiving under an above method' },
  { title: 'Co Host - Credit Card charge to Owner', value: 'Credit Card (Co Host)' },
  { title: 'Co Host - Direct Deposit to AC', value: 'Direct Deposit (Co Host)' },
  { title: 'Direct Deposit', value: 'Direct Deposit' },
  { title: 'Paypal', value: 'Paypal' },
]

const statusItems = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
]

const form = reactive({
  region_id: '',
  w9_on_file: 1,
  first_name: '',
  last_name: '',
  owner_email: '',
  owner_phone: '',
  payment_method: '',
  active: 1,
  owner_payout_information: '',
})

const filters = reactive({
  search: '',
  region_id: '',
  payment_method: '',
  status: null,
  w9_on_file: null,
})

const selectedId = ref(null)
const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = owners.data?.data || owners.data || []

  return Array.isArray(data) ? data : []
})

const displayName = owner => {
  if (owner?.full_name)
    return owner.full_name

  const combined = [owner?.first_name, owner?.last_name].filter(Boolean).join(' ')

  return combined || '—'
}

const propertiesCount = owner => {
  if (owner?.properties_count != null)
    return Number(owner.properties_count)

  if (Array.isArray(owner?.properties))
    return owner.properties.length

  return 0
}

const ownerRegion = owner => owner?.region?.region_name || owner?.region?.name || (owner?.region_id ? `Region #${owner.region_id}` : 'No region')

const paymentMethodLabel = value => {
  const match = paymentMethodItems.find(item => item.value === value)

  return match?.title || value || '—'
}

const isOwnerActive = owner => {
  if (owner?.active === false || owner?.active === 0 || owner?.active === '0')
    return false

  if (owner?.status === false || owner?.status === 0 || owner?.status === '0')
    return false

  return true
}

const ownerInitials = owner => {
  const name = displayName(owner)
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
  const paymentMethod = filters.payment_method === '' || filters.payment_method == null
    ? null
    : filters.payment_method
  const statusFilter = filters.status === '' || filters.status === null || filters.status === undefined
    ? null
    : Number(filters.status)

  return allRows.value.filter(owner => {
    if (searchQuery) {
      const haystack = [
        displayName(owner),
        owner.owner_email,
        owner.owner_phone,
        ownerRegion(owner),
        owner.payment_method,
      ].join(' ').toLowerCase()

      if (!haystack.includes(searchQuery))
        return false
    }

    if (regionId !== null && Number(owner.region_id) !== regionId)
      return false

    if (paymentMethod !== null && owner.payment_method !== paymentMethod)
      return false

    if (statusFilter !== null) {
      const active = isOwnerActive(owner)
      if (statusFilter === 1 && !active)
        return false
      if (statusFilter === 0 && active)
        return false
    }

    if (filters.w9_on_file !== null && filters.w9_on_file !== undefined && filters.w9_on_file !== '') {
      const hasW9 = Boolean(owner.w9_on_file)
      if (filters.w9_on_file === 'yes' && !hasW9)
        return false
      if (filters.w9_on_file === 'no' && hasW9)
        return false
    }

    return true
  })
})

const selectedOwner = computed(() => allRows.value.find(owner => String(owner.id) === String(selectedId.value)) || null)

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

  for (const owner of allRows.value) {
    const id = owner.region_id
    if (id == null || map.has(id))
      continue

    const name = owner.region?.region_name || owner.region?.name || `Region ${id}`
    map.set(id, { title: name, value: id })
  }

  return [...map.values()].sort((a, b) => String(a.title).localeCompare(String(b.title)))
})

const statusFilterItems = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
]

const w9FilterItems = [
  { title: 'W9 on file', value: 'yes' },
  { title: 'W9 missing', value: 'no' },
]

const stats = computed(() => {
  const list = allRows.value
  const withW9 = list.filter(owner => Boolean(owner.w9_on_file))
  const withoutW9 = list.filter(owner => !owner.w9_on_file)
  const withEmail = list.filter(owner => Boolean(owner.owner_email))
  const withPhone = list.filter(owner => Boolean(owner.owner_phone))
  const withRegion = list.filter(owner => owner.region_id != null)
  const totalProperties = list.reduce((sum, owner) => sum + propertiesCount(owner), 0)

  return {
    total: list.length,
    withW9: withW9.length,
    withoutW9: withoutW9.length,
    withEmail: withEmail.length,
    withPhone: withPhone.length,
    withRegion: withRegion.length,
    totalProperties,
  }
})

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter owners'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit owner' : 'Create owner'
  if (panelMode.value === 'detail' && selectedOwner.value)
    return displayName(selectedOwner.value)

  return 'Owner overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = selectedOwner.value ? 'detail' : 'dashboard'

    return
  }

  if (mode === 'create' && panelMode.value !== 'create')
    resetForm()

  panelMode.value = mode
}

const selectOwner = owner => {
  selectedId.value = owner.id
  panelMode.value = 'detail'
}

const clearFilters = () => {
  Object.assign(filters, {
    search: '',
    region_id: '',
    payment_method: '',
    status: null,
    w9_on_file: null,
  })
}

onMounted(async () => {
  await Promise.all([
    owners.load(),
    regions.load().catch(() => null),
  ])
})

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    region_id: '',
    w9_on_file: 1,
    first_name: '',
    last_name: '',
    owner_email: '',
    owner_phone: '',
    payment_method: '',
    active: 1,
    owner_payout_information: '',
  })
}

const edit = owner => {
  selectedId.value = owner.id
  editingId.value = owner.id
  Object.assign(form, {
    region_id: owner.region_id || '',
    w9_on_file: owner.w9_on_file ? 1 : 0,
    first_name: owner.first_name || '',
    last_name: owner.last_name || '',
    owner_email: owner.owner_email || '',
    owner_phone: owner.owner_phone || '',
    payment_method: owner.payment_method || '',
    active: isOwnerActive(owner) ? 1 : 0,
    owner_payout_information: owner.owner_payout_information || '',
  })
  panelMode.value = 'create'
}

const cancelCreate = () => {
  resetForm()
  panelMode.value = selectedOwner.value ? 'detail' : 'dashboard'
}

const submit = async () => {
  try {
    const payload = {
      ...form,
      region_id: form.region_id || null,
      owner_email: form.owner_email || null,
      owner_phone: form.owner_phone || null,
      owner_payout_information: form.owner_payout_information || null,
      w9_on_file: Number(form.w9_on_file) === 1,
      active: Number(form.active) === 1,
      full_name: [form.first_name, form.last_name].filter(Boolean).join(' '),
    }

    const region = regionList.value.find(item => Number(item.id) === Number(payload.region_id))
    if (region)
      payload.region = { id: region.id, region_name: region.region_name }

    if (editingId.value)
      await owners.update(editingId.value, payload)
    else
      await owners.create(payload)

    const savedId = editingId.value || owners.current?.id
    resetForm()
    if (savedId)
      selectedId.value = savedId
    panelMode.value = selectedId.value ? 'detail' : 'dashboard'
    if (!owners.usingMocks)
      await owners.load()
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
    await owners.remove(deletedId)
    confirmDelete.open = false
    if (String(selectedId.value) === String(deletedId)) {
      selectedId.value = null
      panelMode.value = 'dashboard'
    }
    if (!owners.usingMocks)
      await owners.load()
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
      title="Owners"
      subtitle="Manage property owners"
    />

    <VAlert
      v-if="owners.usingMocks"
      type="info"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Showing UI preview data until the owners API is connected.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <VCard class="owner-list-card">
          <VCardItem>
            <VCardTitle>All owners</VCardTitle>
          </VCardItem>

          <VCardText class="pb-2">
            <BaseInput
              v-model="filters.search"
              placeholder="Search owners..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            v-if="owners.loading"
            indeterminate
          />

          <div class="owner-list">
            <VRow dense>
              <VCol
                v-for="owner in rows"
                :key="owner.id"
                cols="12"
                lg="6"
              >
                <button
                  type="button"
                  class="owner-list-item"
                  :class="{ 'owner-list-item--selected': selectedId === owner.id }"
                  @click="selectOwner(owner)"
                >
                  <VAvatar
                    size="52"
                    rounded="lg"
                    class="owner-list-item__thumb"
                    color="primary"
                    variant="tonal"
                  >
                    <VImg
                      v-if="owner.avatar_url"
                      :src="owner.avatar_url"
                      cover
                    />
                    <span
                      v-else
                      class="owner-list-item__initials"
                    >{{ ownerInitials(owner) }}</span>
                  </VAvatar>

                  <div class="owner-list-item__body">
                    <div class="owner-list-item__title text-truncate">
                      {{ displayName(owner) }}
                    </div>
                    <div class="owner-list-item__subtitle text-truncate">
                      {{ ownerRegion(owner) }}
                      <template v-if="owner.owner_email">
                        · {{ owner.owner_email }}
                      </template>
                    </div>

                    <div class="owner-list-item__meta">
                      <span class="owner-list-item__meta-item">
                        <VIcon
                          icon="bx-building-house"
                          size="16"
                        />
                        {{ propertiesCount(owner) }}
                        {{ propertiesCount(owner) === 1 ? 'property' : 'properties' }}
                      </span>
                      <span
                        v-if="owner.payment_method"
                        class="owner-list-item__meta-item"
                      >
                        <VIcon
                          icon="bx-credit-card"
                          size="16"
                        />
                        <span class="text-truncate">{{ owner.payment_method }}</span>
                      </span>
                    </div>

                    <VChip
                      size="x-small"
                      :color="owner.w9_on_file ? 'success' : 'warning'"
                      label
                      class="mt-2"
                    >
                      {{ owner.w9_on_file ? 'W9 on file' : 'W9 missing' }}
                    </VChip>
                  </div>

                  <VIcon
                    icon="bx-chevron-right"
                    size="22"
                    class="owner-list-item__chevron"
                  />
                </button>
              </VCol>
            </VRow>

            <EmptyState
              v-if="!owners.loading && rows.length === 0"
              title="No owners found"
              :description="allRows.length && rows.length === 0 ? 'No owners match the current filters.' : ''"
            />
          </div>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard class="owner-panel-card">
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
                  :variant="panelMode === 'create' && !editingId ? 'flat' : 'tonal'"
                  :color="panelMode === 'create' && !editingId ? 'primary' : undefined"
                  label="Create"
                  prepend-icon="bx-plus"
                  @click="openPanel('create')"
                />
                <template v-if="panelMode === 'detail' && selectedOwner">
                  <BaseButton
                    size="small"
                    color="primary"
                    label="Edit"
                    prepend-icon="bx-edit"
                    @click="edit(selectedOwner)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    prepend-icon="bx-trash"
                    @click="askDelete(selectedOwner.id)"
                  />
                </template>
              </div>
            </template>
          </VCardItem>

          <VCardText class="owner-panel-body">
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of owner records and W9 coverage
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      W9 on file
                    </div>
                    <div class="text-h5">
                      {{ stats.withW9 }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      W9 missing
                    </div>
                    <div class="text-h5">
                      {{ stats.withoutW9 }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With email
                    </div>
                    <div class="text-h5">
                      {{ stats.withEmail }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With phone
                    </div>
                    <div class="text-h5">
                      {{ stats.withPhone }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          Properties linked
                        </div>
                        <div class="text-h5">
                          {{ stats.totalProperties }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-building-house"
                        size="28"
                        class="text-medium-emphasis"
                      />
                    </div>
                  </div>
                </VCol>
              </VRow>
            </div>

            <div v-else-if="panelMode === 'detail' && selectedOwner">
              <div class="d-flex align-center gap-3 mb-3">
                <VAvatar
                  size="48"
                  rounded="lg"
                  color="primary"
                  variant="tonal"
                >
                  <VImg
                    v-if="selectedOwner.avatar_url"
                    :src="selectedOwner.avatar_url"
                    cover
                  />
                  <span
                    v-else
                    class="text-body-1 font-weight-medium"
                  >{{ ownerInitials(selectedOwner) }}</span>
                </VAvatar>
                <div class="min-w-0">
                  <div class="text-subtitle-1 font-weight-medium text-truncate">
                    {{ displayName(selectedOwner) }}
                  </div>
                  <div class="text-caption text-medium-emphasis text-truncate">
                    {{ ownerRegion(selectedOwner) }}
                  </div>
                </div>
              </div>

              <ul class="owner-detail-list owner-detail-list--compact">
                <li>
                  <span class="owner-detail-list__label">Region</span>
                  <span class="owner-detail-list__value">{{ ownerRegion(selectedOwner) }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">W9 on file</span>
                  <span class="owner-detail-list__value">{{ selectedOwner.w9_on_file ? 'Yes' : 'No' }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">First name</span>
                  <span class="owner-detail-list__value">{{ selectedOwner.first_name || '—' }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">Last name</span>
                  <span class="owner-detail-list__value">{{ selectedOwner.last_name || '—' }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">Email</span>
                  <span class="owner-detail-list__value">{{ selectedOwner.owner_email || '—' }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">Phone</span>
                  <span class="owner-detail-list__value">{{ selectedOwner.owner_phone || '—' }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">Payment method</span>
                  <span class="owner-detail-list__value">{{ paymentMethodLabel(selectedOwner.payment_method) }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">Status</span>
                  <span class="owner-detail-list__value">{{ isOwnerActive(selectedOwner) ? 'Active' : 'Inactive' }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">Owner payout information</span>
                  <span class="owner-detail-list__value">{{ selectedOwner.owner_payout_information || '—' }}</span>
                </li>
                <li>
                  <span class="owner-detail-list__label">Properties</span>
                  <span class="owner-detail-list__value">
                    {{ propertiesCount(selectedOwner) }}
                    {{ propertiesCount(selectedOwner) === 1 ? 'property' : 'properties' }}
                  </span>
                </li>
              </ul>
            </div>

            <div v-else-if="panelMode === 'filter'">
              <VForm
                class="owner-filter-form"
                @submit.prevent
              >
                <BaseSelect
                  v-model="filters.region_id"
                  label="Region or City"
                  :items="regionOptions"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.payment_method"
                  label="Payment method"
                  :items="paymentMethodItems"
                  clearable
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
                  v-model="filters.w9_on_file"
                  label="W9 on file"
                  :items="w9FilterItems"
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
                    @click="panelMode = selectedOwner ? 'detail' : 'dashboard'"
                  />
                </div>
              </VForm>
            </div>

            <div v-else-if="panelMode === 'create'">
              <AppAlert :errors="owners.errors" />
              <VForm
                class="owner-form"
                @submit.prevent="submit"
              >
                <BaseSelect
                  v-model="form.region_id"
                  label="Region"
                  :items="regionOptions"
                  clearable
                  :error="owners.errors.region_id"
                />
                <div class="owner-form__field mb-4">
                  <div class="text-body-2 mb-1">
                    W9 on file
                  </div>
                  <VRadioGroup
                    v-model="form.w9_on_file"
                    inline
                    hide-details="auto"
                    :error-messages="owners.errors.w9_on_file || []"
                  >
                    <BaseRadio
                      :value="1"
                      label="Yes"
                      size="small"
                    />
                    <BaseRadio
                      :value="0"
                      label="No"
                      size="small"
                    />
                  </VRadioGroup>
                </div>
                <BaseInput
                  v-model="form.first_name"
                  label="First name"
                  :error="owners.errors.first_name"
                />
                <BaseInput
                  v-model="form.last_name"
                  label="Last name"
                  :error="owners.errors.last_name"
                />
                <BaseInput
                  v-model="form.owner_email"
                  label="Email"
                  type="email"
                  :error="owners.errors.owner_email"
                />
                <BaseInput
                  v-model="form.owner_phone"
                  label="Phone"
                  :error="owners.errors.owner_phone"
                />
                <BaseSelect
                  v-model="form.payment_method"
                  label="Payment method"
                  :items="paymentMethodItems"
                  clearable
                  :error="owners.errors.payment_method"
                />
                <BaseSelect
                  v-model="form.active"
                  label="Status"
                  :items="statusItems"
                  :error="owners.errors.active"
                />
                <BaseTextarea
                  v-model="form.owner_payout_information"
                  label="Owner payout information"
                  :error="owners.errors.owner_payout_information"
                />
                <div class="d-flex flex-wrap gap-2 owner-form__actions">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update owner' : 'Create owner'"
                    :loading="owners.loading"
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
      title="Delete owner?"
      message="This soft-deletes the owner (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="owners.loading"
      @confirm="onDelete"
    />
  </div>
</template>

<style scoped>
.owner-list-card,
.owner-panel-card {
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 160px);
}

.owner-panel-body {
  overflow-y: auto;
}

.owner-filter-form {
  /* Keep outlined floating labels from being clipped by the scroll container. */
  padding-top: 4px;
}

.owner-list {
  overflow-y: auto;
  padding: 0 12px 12px;
}

.owner-list :deep(.v-col) {
  display: flex;
}

.owner-list-item {
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

.owner-list-item:hover {
  background: rgba(105, 108, 255, 0.04);
  border-color: rgba(105, 108, 255, 0.28);
}

.owner-list-item--selected {
  background: rgba(105, 108, 255, 0.1);
  border-color: rgba(105, 108, 255, 0.45);
  box-shadow: 0 2px 8px rgba(105, 108, 255, 0.12);
}

.owner-list-item__thumb {
  flex-shrink: 0;
}

.owner-list-item__initials {
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.02em;
}

.owner-list-item__body {
  flex: 1;
  min-width: 0;
}

.owner-list-item__title {
  font-weight: 600;
  font-size: 0.975rem;
  line-height: 1.3;
  color: rgb(var(--v-theme-on-surface));
}

.owner-list-item__subtitle {
  margin-top: 2px;
  font-size: 0.8125rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.owner-list-item__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 14px;
  margin-top: 8px;
}

.owner-list-item__meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  max-width: 100%;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.owner-list-item__chevron {
  flex-shrink: 0;
  color: rgba(var(--v-theme-on-surface), 0.35);
}

.owner-list-item--selected .owner-list-item__chevron {
  color: rgb(var(--v-theme-primary));
}

.owner-form :deep(.v-input),
.owner-form :deep(.v-selection-control) {
  margin-bottom: 1rem;
}

.owner-form__actions {
  margin-top: 0.25rem;
}

.owner-detail-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.owner-detail-list li {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(75, 70, 92, 0.08);
}

.owner-detail-list li:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.owner-detail-list--compact li {
  padding: 7px 0;
}

.owner-detail-list__label {
  flex-shrink: 0;
  max-width: 45%;
  font-size: 0.8125rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.owner-detail-list__value {
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
