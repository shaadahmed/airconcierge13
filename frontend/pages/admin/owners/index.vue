<script setup>
const owners = useOwnersStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  first_name: '',
  last_name: '',
  full_name: '',
  owner_email: '',
  owner_phone: '',
  region_id: '',
  payment_method: '',
  owner_payout_information: '',
  w9_on_file: false,
})

const filters = reactive({
  name: '',
  email: '',
  region_id: '',
  w9_on_file: null,
})

const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

const allRows = computed(() => {
  const data = owners.data?.data || owners.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()
  const emailQuery = filters.email.trim().toLowerCase()
  const regionId = filters.region_id === '' || filters.region_id === null
    ? null
    : Number(filters.region_id)

  return allRows.value.filter(owner => {
    if (nameQuery) {
      const name = String(owner.full_name || `${owner.first_name || ''} ${owner.last_name || ''}`).toLowerCase()
      if (!name.includes(nameQuery))
        return false
    }

    if (emailQuery) {
      const email = String(owner.owner_email || '').toLowerCase()
      if (!email.includes(emailQuery))
        return false
    }

    if (regionId !== null && Number(owner.region_id) !== regionId)
      return false

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

const regionOptions = computed(() => {
  const map = new Map()

  for (const owner of allRows.value) {
    const id = owner.region_id
    if (id == null)
      continue

    const name = owner.region?.region_name || owner.region?.name || `Region ${id}`
    if (!map.has(id))
      map.set(id, { title: name, value: id })
  }

  return [...map.values()].sort((a, b) => String(a.title).localeCompare(String(b.title)))
})

const w9FilterItems = [
  { title: 'All owners', value: null },
  { title: 'W9 on file', value: 'yes' },
  { title: 'W9 missing', value: 'no' },
]

const displayName = owner => {
  if (owner.full_name)
    return owner.full_name

  const combined = [owner.first_name, owner.last_name].filter(Boolean).join(' ')

  return combined || '—'
}

const stats = computed(() => {
  const list = allRows.value
  const withW9 = list.filter(owner => Boolean(owner.w9_on_file))
  const withoutW9 = list.filter(owner => !owner.w9_on_file)
  const withEmail = list.filter(owner => Boolean(owner.owner_email))
  const withPhone = list.filter(owner => Boolean(owner.owner_phone))
  const withRegion = list.filter(owner => owner.region_id != null)

  return {
    total: list.length,
    withW9: withW9.length,
    withoutW9: withoutW9.length,
    withEmail: withEmail.length,
    withPhone: withPhone.length,
    withRegion: withRegion.length,
  }
})

const statusChartSeries = computed(() => [stats.value.withW9, stats.value.withoutW9])

const statusChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  labels: ['W9 on file', 'W9 missing'],
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
    y: { formatter: value => `${value} owners` },
  },
}))

const activityChartSeries = computed(() => ([
  {
    name: 'Owners',
    data: [
      stats.value.withEmail,
      stats.value.withPhone,
      stats.value.withRegion,
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
    categories: ['Email', 'Phone', 'Region'],
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
    y: { formatter: value => `${value} owners` },
  },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter owners'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit owner' : 'Create owner'

  return 'Owner overview'
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
    region_id: '',
    w9_on_file: null,
  })
}

onMounted(() => owners.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    first_name: '',
    last_name: '',
    full_name: '',
    owner_email: '',
    owner_phone: '',
    region_id: '',
    payment_method: '',
    owner_payout_information: '',
    w9_on_file: false,
  })
}

const edit = owner => {
  editingId.value = owner.id
  Object.assign(form, {
    first_name: owner.first_name || '',
    last_name: owner.last_name || '',
    full_name: owner.full_name || '',
    owner_email: owner.owner_email || '',
    owner_phone: owner.owner_phone || '',
    region_id: owner.region_id || '',
    payment_method: owner.payment_method || '',
    owner_payout_information: owner.owner_payout_information || '',
    w9_on_file: owner.w9_on_file ?? false,
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
      region_id: form.region_id || null,
      owner_email: form.owner_email || null,
      owner_phone: form.owner_phone || null,
      owner_payout_information: form.owner_payout_information || null,
    }

    if (editingId.value)
      await owners.update(editingId.value, payload)
    else
      await owners.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
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
    await owners.remove(confirmDelete.id)
    confirmDelete.open = false
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

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All owners"
          :loading="owners.loading"
          :empty="rows.length === 0"
          :empty-colspan="6"
          empty-title="No owners found"
          :empty-description="allRows.length && rows.length === 0 ? 'No owners match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Region</th>
                <th>W9</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="owner in rows"
            :key="owner.id"
          >
            <td>{{ displayName(owner) }}</td>
            <td>{{ owner.owner_email || '—' }}</td>
            <td>{{ owner.owner_phone || '—' }}</td>
            <td>{{ owner.region?.region_name || owner.region_id || '—' }}</td>
            <td>{{ owner.w9_on_file ? 'Yes' : 'No' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(owner)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(owner.id)"
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
                          Linked to a region
                        </div>
                        <div class="text-h5">
                          {{ stats.withRegion }}
                        </div>
                      </div>
                      <VIcon
                        icon="bx-map"
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
                    W9 coverage
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!owners.loading"
                    title="No owners yet"
                    description="Counts and charts will appear once owners are loaded."
                  />
                </div>

                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    Contact & region
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
                  v-model="filters.region_id"
                  label="Region"
                  :items="regionOptions"
                  clearable
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.w9_on_file"
                  label="W9 status"
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
                    label="Back to overview"
                    @click="panelMode = 'dashboard'"
                  />
                </div>
              </VForm>
            </div>

            <div v-else>
              <AppAlert :errors="owners.errors" />
              <VForm @submit.prevent="submit">
                <BaseInput
                  v-model="form.full_name"
                  label="Full name"
                  :error="owners.errors.full_name"
                />
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
                <BaseInput
                  v-model="form.region_id"
                  label="Region ID"
                  type="number"
                  :error="owners.errors.region_id"
                />
                <BaseInput
                  v-model="form.payment_method"
                  label="Payment method"
                  :error="owners.errors.payment_method"
                />
                <BaseTextarea
                  v-model="form.owner_payout_information"
                  label="Payout information"
                  :error="owners.errors.owner_payout_information"
                />
                <BaseCheckbox
                  v-model="form.w9_on_file"
                  label="W9 on file"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
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
