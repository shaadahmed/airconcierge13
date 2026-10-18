<script setup>
import { US_STATE_OPTIONS } from '@/constants/properties'

const users = useUsersStore()
const owners = useOwnersStore()
const managers = useManagersStore()
const regions = useRegionsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail' | 'create'>} */
const panelMode = ref('dashboard')

const roleItems = [
  { title: 'Super Admin', value: 'superadmin' },
  { title: 'Admin', value: 'admin' },
  { title: 'Manager', value: 'manager' },
  { title: 'Owner', value: 'owner' },
  { title: 'Cleaner', value: 'cleaner' },
  { title: 'Maintenance', value: 'maintenance' },
]

const statusItems = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
]

const paymentMethodItems = [
  { title: 'Another owner is receiving under an above method', value: 'Another owner is receiving under an above method' },
  { title: 'Co Host - Credit Card charge to Owner', value: 'Credit Card (Co Host)' },
  { title: 'Co Host - Direct Deposit to AC', value: 'Direct Deposit (Co Host)' },
  { title: 'Direct Deposit', value: 'Direct Deposit' },
  { title: 'Paypal', value: 'Paypal' },
]

const salaryTypeItems = [
  { title: 'Salary', value: 0 },
  { title: 'Salary + Commissions', value: 1 },
  { title: 'Commission Only', value: 2 },
]

const employmentStatusItems = [
  { title: 'Employee', value: 0 },
  { title: 'Contractor', value: 1 },
]

const emptyNewOwner = () => ({
  region_id: '',
  w9_on_file: 1,
  payment_method: '',
  status: 1,
  owner_payout_information: '',
})

const emptyNewManager = () => ({
  salary_type: '',
  compensation_structure: '',
  employment_status: '',
  is_active: 1,
})

const form = reactive({
  firstname: '',
  lastname: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
  phone: '',
  ext: '',
  fax: '',
  website: '',
  address: '',
  city: '',
  state: '',
  zipcode: '',
  role: '',
  active: 1,
  owner_ids: [],
  manager_ids: [],
  showNewOwner: false,
  showNewManager: false,
  new_owner: emptyNewOwner(),
  new_manager: emptyNewManager(),
})

const filters = reactive({
  search: '',
  role: '',
  status: null,
  city: '',
  state: '',
})

const selectedId = ref(null)
const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = users.data?.data || users.data || []

  return Array.isArray(data) ? data : []
})

const roleLabel = value => {
  if (value == null || value === '')
    return '—'

  const known = roleItems.find(item => String(item.value) === String(value))
  if (known)
    return known.title

  return String(value)
}

const displayName = user => {
  const combined = [user?.firstname, user?.lastname].filter(Boolean).join(' ')
  if (combined)
    return combined

  if (user?.name)
    return user.name

  return user?.username || '—'
}

const userRoleValue = user => user?.role?.value || user?.role || ''

const isUserActive = user => {
  if (user?.active === false || user?.active === 0 || user?.active === '0')
    return false

  return true
}

const userInitials = user => {
  const name = displayName(user)
  if (!name || name === '—')
    return '?'

  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map(part => part[0]?.toUpperCase() || '')
    .join('')
}

const formatLastLogin = value => {
  if (!value)
    return '—'

  const date = new Date(value)
  if (Number.isNaN(date.getTime()))
    return String(value)

  return date.toLocaleString()
}

const linkedOwnerNames = user => {
  const list = Array.isArray(user?.owners) ? user.owners : []
  if (!list.length)
    return '—'

  return list
    .map(owner => [owner.first_name, owner.last_name].filter(Boolean).join(' ') || owner.name || `Owner #${owner.id}`)
    .join(', ')
}

const linkedManagerNames = user => {
  const list = Array.isArray(user?.managers) ? user.managers : []
  if (!list.length)
    return '—'

  return list
    .map(manager => [manager.first_name, manager.last_name].filter(Boolean).join(' ') || manager.name || `Manager #${manager.id}`)
    .join(', ')
}

const rows = computed(() => {
  const searchQuery = filters.search.trim().toLowerCase()
  const roleFilter = filters.role === '' || filters.role == null ? null : String(filters.role)
  const statusFilter = filters.status === '' || filters.status === null || filters.status === undefined
    ? null
    : Number(filters.status)
  const cityQuery = filters.city.trim().toLowerCase()
  const stateFilter = filters.state === '' || filters.state == null ? null : String(filters.state)

  return allRows.value.filter(user => {
    if (searchQuery) {
      const haystack = [
        displayName(user),
        user.username,
        user.email,
        user.phone,
        user.ext,
        user.city,
        user.state,
        roleLabel(userRoleValue(user)),
        user.role_label,
      ].join(' ').toLowerCase()

      if (!haystack.includes(searchQuery))
        return false
    }

    if (roleFilter !== null && String(userRoleValue(user)) !== roleFilter)
      return false

    if (statusFilter !== null) {
      const active = isUserActive(user)
      if (statusFilter === 1 && !active)
        return false
      if (statusFilter === 0 && active)
        return false
    }

    if (cityQuery && !String(user.city || '').toLowerCase().includes(cityQuery))
      return false

    if (stateFilter !== null && String(user.state || '') !== stateFilter)
      return false

    return true
  })
})

const selectedUser = computed(() => allRows.value.find(user => String(user.id) === String(selectedId.value)) || null)

const ownerList = computed(() => {
  const data = owners.data?.data || owners.data || []

  return Array.isArray(data) ? data : []
})

const managerList = computed(() => {
  const data = managers.data?.data || managers.data || []

  return Array.isArray(data) ? data : []
})

const ownerOptions = computed(() => ownerList.value.map(owner => ({
  title: [owner.first_name, owner.last_name].filter(Boolean).join(' ') || owner.full_name || `Owner #${owner.id}`,
  value: owner.id,
})))

const managerOptions = computed(() => managerList.value.map(manager => ({
  title: [manager.first_name, manager.last_name].filter(Boolean).join(' ') || `Manager #${manager.id}`,
  value: manager.id,
})))

const regionList = computed(() => {
  const data = regions.data?.data || regions.data || []

  return Array.isArray(data) ? data : []
})

const regionOptions = computed(() => regionList.value.map(region => ({
  title: region.name || `Region ${region.id}`,
  value: region.id,
})))

const isOwnerRole = computed(() => String(form.role) === 'owner')
const isManagerRole = computed(() => String(form.role) === 'manager')

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(user => isUserActive(user))
  const inactive = list.filter(user => !isUserActive(user))
  const withPhone = list.filter(user => Boolean(user.phone))
  const ownersCount = list.filter(user => String(userRoleValue(user)) === 'owner').length
  const managersCount = list.filter(user => String(userRoleValue(user)) === 'manager').length
  const adminsCount = list.filter(user => ['superadmin', 'admin'].includes(String(userRoleValue(user)))).length

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    withPhone: withPhone.length,
    owners: ownersCount,
    managers: managersCount,
    admins: adminsCount,
  }
})

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter users'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit user' : 'Create user'
  if (panelMode.value === 'detail' && selectedUser.value)
    return displayName(selectedUser.value)

  return 'Users overview'
})

watch(() => form.role, role => {
  if (String(role) !== 'owner') {
    form.showNewOwner = false
    form.owner_ids = []
    form.new_owner = emptyNewOwner()
  }

  if (String(role) !== 'manager') {
    form.showNewManager = false
    form.manager_ids = []
    form.new_manager = emptyNewManager()
  }
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = selectedUser.value ? 'detail' : 'dashboard'

    return
  }

  if (mode === 'create' && panelMode.value !== 'create')
    resetForm()

  panelMode.value = mode
}

const selectUser = user => {
  selectedId.value = user.id
  panelMode.value = 'detail'
}

const clearFilters = () => {
  Object.assign(filters, {
    search: '',
    role: '',
    status: null,
    city: '',
    state: '',
  })
}

onMounted(async () => {
  await Promise.all([
    users.load(),
    owners.load().catch(() => null),
    managers.load().catch(() => null),
    regions.load().catch(() => null),
  ])
})

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    firstname: '',
    lastname: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: '',
    phone: '',
    ext: '',
    fax: '',
    website: '',
    address: '',
    city: '',
    state: '',
    zipcode: '',
    role: '',
    active: 1,
    owner_ids: [],
    manager_ids: [],
    showNewOwner: false,
    showNewManager: false,
    new_owner: emptyNewOwner(),
    new_manager: emptyNewManager(),
  })
}

const splitName = user => {
  if (user?.firstname || user?.lastname) {
    return {
      firstname: user.firstname || '',
      lastname: user.lastname || '',
    }
  }

  const parts = String(user?.name || '').trim().split(/\s+/)
  if (!parts.length || (parts.length === 1 && !parts[0]))
    return { firstname: '', lastname: '' }

  if (parts.length === 1)
    return { firstname: parts[0], lastname: '' }

  return {
    firstname: parts[0],
    lastname: parts.slice(1).join(' '),
  }
}

const edit = user => {
  selectedId.value = user.id
  editingId.value = user.id
  const names = splitName(user)
  const ownerIds = Array.isArray(user.owners)
    ? user.owners.map(owner => owner.id)
    : (Array.isArray(user.owner_ids) ? user.owner_ids : [])
  const managerIds = Array.isArray(user.managers)
    ? user.managers.map(manager => manager.id)
    : (Array.isArray(user.manager_ids) ? user.manager_ids : [])

  Object.assign(form, {
    firstname: names.firstname,
    lastname: names.lastname,
    email: user.email || '',
    username: user.username || user.email || '',
    password: '',
    password_confirmation: '',
    phone: user.phone || '',
    ext: user.ext || '',
    fax: user.fax || '',
    website: user.website || '',
    address: user.address || '',
    city: user.city || '',
    state: user.state || '',
    zipcode: user.zipcode || '',
    role: userRoleValue(user) || '',
    active: isUserActive(user) ? 1 : 0,
    owner_ids: ownerIds,
    manager_ids: managerIds,
    showNewOwner: false,
    showNewManager: false,
    new_owner: emptyNewOwner(),
    new_manager: emptyNewManager(),
  })
  panelMode.value = 'create'
}

const cancelCreate = () => {
  resetForm()
  panelMode.value = selectedUser.value ? 'detail' : 'dashboard'
}

const toggleNewOwner = () => {
  form.showNewOwner = !form.showNewOwner
  if (form.showNewOwner)
    form.owner_ids = []
  else
    form.new_owner = emptyNewOwner()
}

const toggleNewManager = () => {
  form.showNewManager = !form.showNewManager
  if (form.showNewManager)
    form.manager_ids = []
  else
    form.new_manager = emptyNewManager()
}

const buildLinkedOwners = () => {
  if (!isOwnerRole.value)
    return []

  if (form.showNewOwner) {
    return [{
      id: `new-${Date.now()}`,
      first_name: form.firstname,
      last_name: form.lastname,
      ...form.new_owner,
    }]
  }

  return ownerList.value.filter(owner => form.owner_ids.map(Number).includes(Number(owner.id)))
}

const buildLinkedManagers = () => {
  if (!isManagerRole.value)
    return []

  if (form.showNewManager) {
    return [{
      id: `new-${Date.now()}`,
      first_name: form.firstname,
      last_name: form.lastname,
      ...form.new_manager,
    }]
  }

  return managerList.value.filter(manager => form.manager_ids.map(Number).includes(Number(manager.id)))
}

const submit = async () => {
  try {
    if (!editingId.value && !form.password) {
      users.errors = { password: ['Password is required when creating a user.'] }

      return
    }

    if (form.password && form.password !== form.password_confirmation) {
      users.errors = { password_confirmation: ['Password confirmation does not match.'] }

      return
    }

    const payload = {
      firstname: form.firstname || null,
      lastname: form.lastname || null,
      email: form.email || null,
      username: form.username || null,
      phone: form.phone || null,
      ext: form.ext || null,
      fax: form.fax || null,
      website: form.website || null,
      address: form.address || null,
      city: form.city || null,
      state: form.state || null,
      zipcode: form.zipcode || null,
      role: form.role || null,
      role_label: roleLabel(form.role),
      active: Number(form.active) === 1 ? 1 : 0,
      owner_ids: isOwnerRole.value && !form.showNewOwner ? form.owner_ids : [],
      manager_ids: isManagerRole.value && !form.showNewManager ? form.manager_ids : [],
      owners: buildLinkedOwners(),
      managers: buildLinkedManagers(),
      new_owner: form.showNewOwner ? { ...form.new_owner } : null,
      new_manager: form.showNewManager ? { ...form.new_manager } : null,
    }

    if (form.password)
      payload.password = form.password

    if (editingId.value)
      await users.update(editingId.value, payload)
    else
      await users.create(payload)

    const savedId = editingId.value || users.current?.id
    resetForm()
    if (savedId)
      selectedId.value = savedId
    panelMode.value = selectedId.value ? 'detail' : 'dashboard'
    if (!users.usingMocks)
      await users.load()
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
    await users.remove(deletedId)
    confirmDelete.open = false
    if (String(selectedId.value) === String(deletedId)) {
      selectedId.value = null
      panelMode.value = 'dashboard'
    }
    if (!users.usingMocks)
      await users.load()
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
      title="Manage Users"
      subtitle="Administration user accounts"
    />

    <VAlert
      v-if="users.usingMocks"
      type="info"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Showing UI preview data until the users API exposes full profile fields.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <VCard class="staff-list-card">
          <VCardItem>
            <VCardTitle>Users list</VCardTitle>
          </VCardItem>

          <VCardText class="pb-2">
            <BaseInput
              v-model="filters.search"
              placeholder="Search users..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            v-if="users.loading"
            indeterminate
          />

          <div class="staff-list">
            <VRow dense>
              <VCol
                v-for="user in rows"
                :key="user.id"
                cols="12"
                lg="6"
              >
                <button
                  type="button"
                  class="staff-list-item"
                  :class="{
                    'staff-list-item--selected': selectedId === user.id,
                    'staff-list-item--inactive': !isUserActive(user),
                  }"
                  @click="selectUser(user)"
                >
                  <VAvatar
                    size="52"
                    rounded="lg"
                    class="staff-list-item__thumb"
                    color="primary"
                    variant="tonal"
                  >
                    <span class="staff-list-item__initials">{{ userInitials(user) }}</span>
                  </VAvatar>

                  <div class="staff-list-item__body">
                    <div class="staff-list-item__title text-truncate">
                      {{ displayName(user) }}
                    </div>
                    <div class="staff-list-item__subtitle text-truncate">
                      {{ user.username || '—' }}
                      <template v-if="user.email">
                        · {{ user.email }}
                      </template>
                    </div>

                    <div class="staff-list-item__meta">
                      <span
                        v-if="user.phone"
                        class="staff-list-item__meta-item"
                      >
                        <VIcon
                          icon="bx-phone"
                          size="16"
                        />
                        {{ user.phone }}
                        <template v-if="user.ext">
                          x{{ user.ext }}
                        </template>
                      </span>
                      <span class="staff-list-item__meta-item">
                        <VIcon
                          icon="bx-group"
                          size="16"
                        />
                        <span class="text-truncate">{{ user.role_label || roleLabel(userRoleValue(user)) }}</span>
                      </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                      <VChip
                        size="x-small"
                        :color="isUserActive(user) ? 'success' : 'secondary'"
                        label
                      >
                        {{ isUserActive(user) ? 'Active' : 'Inactive' }}
                      </VChip>
                      <VChip
                        size="x-small"
                        color="info"
                        label
                        variant="tonal"
                      >
                        {{ formatLastLogin(user.last_login) }}
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
              v-if="!users.loading && rows.length === 0"
              title="No users found"
              :description="allRows.length && rows.length === 0 ? 'No users match the current filters.' : ''"
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
                <template v-if="panelMode === 'detail' && selectedUser">
                  <BaseButton
                    size="small"
                    color="primary-light"
                    variant="flat"
                    label="Edit"
                    prepend-icon="bx-edit"
                    @click="edit(selectedUser)"
                  />
                  <BaseButton
                    size="small"
                    label="Delete"
                    prepend-icon="bx-trash"
                    @click="askDelete(selectedUser.id)"
                  />
                </template>
              </div>
            </template>
          </VCardItem>

          <VCardText class="staff-panel-body">
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of administration user accounts
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
                      Admins
                    </div>
                    <div class="text-h5">
                      {{ stats.admins }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Owners
                    </div>
                    <div class="text-h5">
                      {{ stats.owners }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-medium-emphasis">
                          Managers
                        </div>
                        <div class="text-h5">
                          {{ stats.managers }}
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

            <div v-else-if="panelMode === 'detail' && selectedUser">
              <div class="d-flex align-center gap-3 mb-3">
                <VAvatar
                  size="48"
                  rounded="lg"
                  color="primary"
                  variant="tonal"
                >
                  <span class="text-body-1 font-weight-medium">{{ userInitials(selectedUser) }}</span>
                </VAvatar>
                <div class="min-w-0">
                  <div class="text-subtitle-1 font-weight-medium text-truncate">
                    {{ displayName(selectedUser) }}
                  </div>
                  <div class="text-caption text-medium-emphasis text-truncate">
                    {{ selectedUser.role_label || roleLabel(userRoleValue(selectedUser)) }}
                  </div>
                </div>
              </div>

              <ul class="staff-detail-list staff-detail-list--compact">
                <li>
                  <span class="staff-detail-list__label">Username</span>
                  <span class="staff-detail-list__value">{{ selectedUser.username || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">First name</span>
                  <span class="staff-detail-list__value">{{ splitName(selectedUser).firstname || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Last name</span>
                  <span class="staff-detail-list__value">{{ splitName(selectedUser).lastname || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Email</span>
                  <span class="staff-detail-list__value">{{ selectedUser.email || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Phone</span>
                  <span class="staff-detail-list__value">
                    {{ selectedUser.phone || '—' }}
                    <template v-if="selectedUser.ext">
                      · Ext. {{ selectedUser.ext }}
                    </template>
                  </span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Fax</span>
                  <span class="staff-detail-list__value">{{ selectedUser.fax || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Website</span>
                  <span class="staff-detail-list__value">{{ selectedUser.website || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Address</span>
                  <span class="staff-detail-list__value">{{ selectedUser.address || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">City</span>
                  <span class="staff-detail-list__value">{{ selectedUser.city || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">State</span>
                  <span class="staff-detail-list__value">{{ selectedUser.state || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Zipcode</span>
                  <span class="staff-detail-list__value">{{ selectedUser.zipcode || '—' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">User group</span>
                  <span class="staff-detail-list__value">{{ selectedUser.role_label || roleLabel(userRoleValue(selectedUser)) }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Status</span>
                  <span class="staff-detail-list__value">{{ isUserActive(selectedUser) ? 'Active' : 'Inactive' }}</span>
                </li>
                <li>
                  <span class="staff-detail-list__label">Last login</span>
                  <span class="staff-detail-list__value">{{ formatLastLogin(selectedUser.last_login) }}</span>
                </li>
                <li v-if="String(userRoleValue(selectedUser)) === 'owner'">
                  <span class="staff-detail-list__label">Owners</span>
                  <span class="staff-detail-list__value">{{ linkedOwnerNames(selectedUser) }}</span>
                </li>
                <li v-if="String(userRoleValue(selectedUser)) === 'manager'">
                  <span class="staff-detail-list__label">Managers</span>
                  <span class="staff-detail-list__value">{{ linkedManagerNames(selectedUser) }}</span>
                </li>
              </ul>
            </div>

            <div v-else-if="panelMode === 'filter'">
              <VForm
                class="staff-filter-form"
                @submit.prevent
              >
                <BaseSelect
                  v-model="filters.role"
                  label="User group"
                  :items="roleItems"
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
                <BaseInput
                  v-model="filters.city"
                  label="City"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.state"
                  label="State"
                  :items="US_STATE_OPTIONS"
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
                    @click="panelMode = selectedUser ? 'detail' : 'dashboard'"
                  />
                </div>
              </VForm>
            </div>

            <div v-else-if="panelMode === 'create'">
              <AppAlert :errors="users.errors" />
              <VForm
                class="staff-form"
                @submit.prevent="submit"
              >
                <BaseInput
                  v-model="form.firstname"
                  label="First name"
                  :error="users.errors.firstname"
                />
                <BaseInput
                  v-model="form.lastname"
                  label="Last name"
                  :error="users.errors.lastname"
                />
                <BaseInput
                  v-model="form.email"
                  label="Email"
                  type="email"
                  :error="users.errors.email"
                />
                <BaseInput
                  v-model="form.username"
                  label="Username"
                  :error="users.errors.username"
                />
                <BaseInput
                  v-model="form.password"
                  :label="editingId ? 'Password (leave blank to keep)' : 'Password'"
                  type="password"
                  autocomplete="new-password"
                  :error="users.errors.password"
                />
                <BaseInput
                  v-model="form.password_confirmation"
                  label="Confirm password"
                  type="password"
                  autocomplete="new-password"
                  :error="users.errors.password_confirmation"
                />

                <VDivider class="mb-4" />

                <BaseInput
                  v-model="form.phone"
                  label="Phone"
                  :error="users.errors.phone"
                />
                <BaseInput
                  v-model="form.ext"
                  label="Ext."
                  :error="users.errors.ext"
                />
                <BaseInput
                  v-model="form.fax"
                  label="Fax"
                  :error="users.errors.fax"
                />
                <BaseInput
                  v-model="form.website"
                  label="Website"
                  :error="users.errors.website"
                />
                <BaseInput
                  v-model="form.address"
                  label="Address"
                  :error="users.errors.address"
                />
                <BaseInput
                  v-model="form.city"
                  label="City"
                  :error="users.errors.city"
                />
                <BaseSelect
                  v-model="form.state"
                  label="State"
                  :items="US_STATE_OPTIONS"
                  clearable
                  :error="users.errors.state"
                />
                <BaseInput
                  v-model="form.zipcode"
                  label="Zipcode"
                  :error="users.errors.zipcode"
                />
                <BaseSelect
                  v-model="form.role"
                  label="User group"
                  :items="roleItems"
                  :error="users.errors.role"
                />
                <BaseSelect
                  v-model="form.active"
                  label="Status"
                  :items="statusItems"
                  :error="users.errors.active"
                />

                <template v-if="isOwnerRole">
                  <VDivider class="mb-4" />
                  <BaseSelect
                    v-model="form.owner_ids"
                    label="Owners"
                    :items="ownerOptions"
                    multiple
                    chips
                    clearable
                    :disabled="form.showNewOwner"
                    :error="users.errors.owner_ids || users.errors.owners"
                  />
                  <div class="d-flex align-center gap-2 mb-4">
                    <span class="text-caption text-medium-emphasis">or</span>
                    <BaseButton
                      type="button"
                      size="small"
                      :variant="form.showNewOwner ? 'flat' : 'tonal'"
                      :color="form.showNewOwner ? 'primary' : undefined"
                      :label="form.showNewOwner ? 'Hide new owner' : 'Add new owner'"
                      @click="toggleNewOwner"
                    />
                  </div>

                  <div
                    v-if="form.showNewOwner"
                    class="staff-nested-form mb-4"
                  >
                    <div class="text-caption text-medium-emphasis text-center mb-3">
                      — New owner fields —
                    </div>
                    <BaseSelect
                      v-model="form.new_owner.region_id"
                      label="Region"
                      :items="regionOptions"
                      clearable
                    />
                    <div class="mb-4">
                      <div class="text-body-2 mb-1">
                        W9 on file
                      </div>
                      <VRadioGroup
                        v-model="form.new_owner.w9_on_file"
                        inline
                        hide-details="auto"
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
                    <BaseSelect
                      v-model="form.new_owner.payment_method"
                      label="Payment method"
                      :items="paymentMethodItems"
                      clearable
                    />
                    <BaseSelect
                      v-model="form.new_owner.status"
                      label="Owner status"
                      :items="statusItems"
                    />
                    <BaseTextarea
                      v-model="form.new_owner.owner_payout_information"
                      label="Owner payout information"
                    />
                  </div>
                </template>

                <template v-if="isManagerRole">
                  <VDivider class="mb-4" />
                  <BaseSelect
                    v-model="form.manager_ids"
                    label="Managers"
                    :items="managerOptions"
                    multiple
                    chips
                    clearable
                    :disabled="form.showNewManager"
                    :error="users.errors.manager_ids || users.errors.managers"
                  />
                  <div class="d-flex align-center gap-2 mb-4">
                    <span class="text-caption text-medium-emphasis">or</span>
                    <BaseButton
                      type="button"
                      size="small"
                      :variant="form.showNewManager ? 'flat' : 'tonal'"
                      :color="form.showNewManager ? 'primary' : undefined"
                      :label="form.showNewManager ? 'Hide new manager' : 'Add new manager'"
                      @click="toggleNewManager"
                    />
                  </div>

                  <div
                    v-if="form.showNewManager"
                    class="staff-nested-form mb-4"
                  >
                    <div class="text-caption text-medium-emphasis text-center mb-3">
                      — New manager fields —
                    </div>
                    <BaseSelect
                      v-model="form.new_manager.salary_type"
                      label="Compensation structure"
                      :items="salaryTypeItems"
                      clearable
                    />
                    <BaseTextarea
                      v-model="form.new_manager.compensation_structure"
                      label="Compensation structure details"
                    />
                    <BaseSelect
                      v-model="form.new_manager.employment_status"
                      label="Employment status"
                      :items="employmentStatusItems"
                      clearable
                    />
                    <BaseSelect
                      v-model="form.new_manager.is_active"
                      label="Manager status"
                      :items="statusItems"
                    />
                  </div>
                </template>

                <div class="d-flex flex-wrap gap-2 staff-form__actions">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update user' : 'Create user'"
                    :loading="users.loading"
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
      title="Delete user?"
      message="This removes the user account from the directory."
      confirm-label="Delete"
      confirm-color="error"
      :loading="users.loading"
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

.staff-list-item--inactive {
  background: rgba(255, 182, 193, 0.18);
}

.staff-list-item--inactive.staff-list-item--selected {
  background: rgba(255, 182, 193, 0.32);
  border-color: rgba(220, 120, 140, 0.55);
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

.staff-nested-form {
  padding: 12px;
  border: 1px dashed rgba(75, 70, 92, 0.2);
  border-radius: 10px;
  background: rgba(75, 70, 92, 0.02);
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
