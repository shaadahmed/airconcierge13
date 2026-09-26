<script setup>
const managers = useManagersStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  regional_manager: true,
  is_active: true,
  salary_type: '',
  employment_status: '',
  home_address: '',
  compensation_structure: '',
  city: '',
  state: '',
  zip: '',
})

const filters = reactive({
  name: '',
  email: '',
  is_active: null,
  regional_manager: null,
})

const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = managers.data?.data || managers.data || []

  return Array.isArray(data) ? data : []
})

const displayName = manager => {
  const combined = [manager.first_name, manager.last_name].filter(Boolean).join(' ')

  return combined || '—'
}

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()
  const emailQuery = filters.email.trim().toLowerCase()

  return allRows.value.filter(manager => {
    if (nameQuery) {
      const name = displayName(manager).toLowerCase()
      if (!name.includes(nameQuery))
        return false
    }

    if (emailQuery) {
      const email = String(manager.email || '').toLowerCase()
      if (!email.includes(emailQuery))
        return false
    }

    if (filters.is_active !== null && filters.is_active !== undefined && filters.is_active !== '') {
      const isActive = Boolean(manager.is_active)
      if (filters.is_active === 'active' && !isActive)
        return false
      if (filters.is_active === 'inactive' && isActive)
        return false
    }

    if (filters.regional_manager !== null && filters.regional_manager !== undefined && filters.regional_manager !== '') {
      const isRegional = Boolean(manager.regional_manager)
      if (filters.regional_manager === 'yes' && !isRegional)
        return false
      if (filters.regional_manager === 'no' && isRegional)
        return false
    }

    return true
  })
})

const statusFilterItems = [
  { title: 'All statuses', value: null },
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
]

const regionalFilterItems = [
  { title: 'All managers', value: null },
  { title: 'Regional manager', value: 'yes' },
  { title: 'Not regional', value: 'no' },
]

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(manager => Boolean(manager.is_active))
  const inactive = list.filter(manager => !manager.is_active)
  const regional = list.filter(manager => Boolean(manager.regional_manager))
  const withEmail = list.filter(manager => Boolean(manager.email))
  const withPhone = list.filter(manager => Boolean(manager.phone))

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    regional: regional.length,
    withEmail: withEmail.length,
    withPhone: withPhone.length,
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
    y: { formatter: value => `${value} managers` },
  },
}))

const activityChartSeries = computed(() => ([
  {
    name: 'Managers',
    data: [
      stats.value.regional,
      stats.value.withEmail,
      stats.value.withPhone,
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
    categories: ['Regional', 'Email', 'Phone'],
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
    y: { formatter: value => `${value} managers` },
  },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter managers'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit manager' : 'Create manager'

  return 'Manager overview'
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
    email: '',
    is_active: null,
    regional_manager: null,
  })
}

onMounted(() => managers.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    regional_manager: true,
    is_active: true,
    salary_type: '',
    employment_status: '',
    home_address: '',
    compensation_structure: '',
    city: '',
    state: '',
    zip: '',
  })
}

const edit = manager => {
  editingId.value = manager.id
  Object.assign(form, {
    first_name: manager.first_name || '',
    last_name: manager.last_name || '',
    email: manager.email || '',
    phone: manager.phone || '',
    regional_manager: manager.regional_manager ?? true,
    is_active: manager.is_active ?? true,
    salary_type: manager.salary_type ?? '',
    employment_status: manager.employment_status ?? '',
    home_address: manager.home_address || '',
    compensation_structure: manager.compensation_structure || '',
    city: manager.city || '',
    state: manager.state || '',
    zip: manager.zip || '',
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
      email: form.email || null,
      phone: form.phone || null,
      home_address: form.home_address || null,
      compensation_structure: form.compensation_structure || null,
      city: form.city || null,
      state: form.state || null,
      zip: form.zip || null,
      salary_type: form.salary_type === '' || form.salary_type === null
        ? null
        : Number(form.salary_type),
      employment_status: form.employment_status === '' || form.employment_status === null
        ? null
        : Number(form.employment_status),
    }

    if (editingId.value)
      await managers.update(editingId.value, payload)
    else
      await managers.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
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
    await managers.remove(confirmDelete.id)
    confirmDelete.open = false
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

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All managers"
          :loading="managers.loading"
          :empty="rows.length === 0"
          :empty-colspan="6"
          empty-title="No managers found"
          :empty-description="allRows.length && rows.length === 0 ? 'No managers match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Regional</th>
                <th>Active</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="manager in rows"
            :key="manager.id"
          >
            <td>{{ displayName(manager) }}</td>
            <td>{{ manager.email || '—' }}</td>
            <td>{{ manager.phone || '—' }}</td>
            <td>{{ manager.regional_manager ? 'Yes' : 'No' }}</td>
            <td>{{ manager.is_active ? 'Yes' : 'No' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(manager)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(manager.id)"
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
                      Regional
                    </div>
                    <div class="text-h5">
                      {{ stats.regional }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With email
                    </div>
                    <div class="text-h5">
                      {{ stats.withEmail }}
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
                    v-else-if="!managers.loading"
                    title="No managers yet"
                    description="Counts and charts will appear once managers are loaded."
                  />
                </div>

                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    Role & contact
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
                  v-model="filters.email"
                  label="Email contains"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.is_active"
                  label="Status"
                  :items="statusFilterItems"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.regional_manager"
                  label="Regional role"
                  :items="regionalFilterItems"
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
              <AppAlert :errors="managers.errors" />
              <VForm @submit.prevent="submit">
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
                <BaseCheckbox
                  v-model="form.regional_manager"
                  label="Regional manager"
                />
                <BaseCheckbox
                  v-model="form.is_active"
                  label="Active"
                />
                <BaseInput
                  v-model="form.salary_type"
                  label="Salary type"
                  type="number"
                  :error="managers.errors.salary_type"
                />
                <BaseInput
                  v-model="form.employment_status"
                  label="Employment status"
                  type="number"
                  :error="managers.errors.employment_status"
                />
                <BaseInput
                  v-model="form.home_address"
                  label="Home address"
                  :error="managers.errors.home_address"
                />
                <BaseTextarea
                  v-model="form.compensation_structure"
                  label="Compensation structure"
                  :error="managers.errors.compensation_structure"
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
                <div class="d-flex flex-wrap gap-2 mt-2">
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
