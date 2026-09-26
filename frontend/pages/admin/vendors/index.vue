<script setup>
const vendors = useVendorsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  name: '',
  email: '',
  password: '',
  active: true,
})

const filters = reactive({
  name: '',
  email: '',
  active: null,
})

const editingId = ref(null)

const allRows = computed(() => {
  const data = vendors.data?.data || vendors.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()
  const emailQuery = filters.email.trim().toLowerCase()

  return allRows.value.filter(vendor => {
    if (nameQuery) {
      const name = String(vendor.name || '').toLowerCase()
      if (!name.includes(nameQuery))
        return false
    }

    if (emailQuery) {
      const email = String(vendor.email || '').toLowerCase()
      if (!email.includes(emailQuery))
        return false
    }

    if (filters.active !== null && filters.active !== undefined && filters.active !== '') {
      const isActive = Boolean(vendor.active)
      if (filters.active === 'active' && !isActive)
        return false
      if (filters.active === 'inactive' && isActive)
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

const monthBounds = computed(() => {
  const now = new Date()
  const start = new Date(now.getFullYear(), now.getMonth(), 1)
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999)

  return { start, end, label: start.toLocaleString(undefined, { month: 'long', year: 'numeric' }) }
})

const parseDate = value => {
  if (!value)
    return null

  const date = new Date(value)

  return Number.isNaN(date.getTime()) ? null : date
}

const isInCurrentMonth = value => {
  const date = parseDate(value)
  if (!date)
    return false

  const { start, end } = monthBounds.value

  return date >= start && date <= end
}

const stats = computed(() => {
  const list = allRows.value
  const active = list.filter(vendor => Boolean(vendor.active))
  const inactive = list.filter(vendor => !vendor.active)
  const newThisMonth = list.filter(vendor => isInCurrentMonth(vendor.created_at))
  const withEmail = list.filter(vendor => Boolean(vendor.email))

  return {
    total: list.length,
    active: active.length,
    inactive: inactive.length,
    newThisMonth: newThisMonth.length,
    withEmail: withEmail.length,
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
    y: { formatter: value => `${value} vendors` },
  },
}))

const activityChartSeries = computed(() => ([
  {
    name: 'Vendors',
    data: [
      stats.value.newThisMonth,
      stats.value.withEmail,
      stats.value.active,
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
    categories: ['New', 'Email', 'Active'],
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
    y: { formatter: value => `${value} vendors` },
  },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter vendors'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit vendor' : 'Create vendor'

  return 'Vendor overview'
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
    active: null,
  })
}

onMounted(() => vendors.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    name: '',
    email: '',
    password: '',
    active: true,
  })
}

const edit = vendor => {
  editingId.value = vendor.id
  Object.assign(form, {
    name: vendor.name || '',
    email: vendor.email || '',
    password: '',
    active: vendor.active ?? true,
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
      name: form.name,
      email: form.email,
      active: form.active,
    }

    if (form.password || !editingId.value)
      payload.password = form.password

    if (editingId.value)
      await vendors.update(editingId.value, payload)
    else
      await vendors.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
    await vendors.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Vendors"
      subtitle="Manage cleaner / vendor accounts"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All vendors"
          :loading="vendors.loading"
          :empty="rows.length === 0"
          :empty-colspan="4"
          empty-title="No vendors found"
          :empty-description="allRows.length && rows.length === 0 ? 'No vendors match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Active</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="vendor in rows"
            :key="vendor.id"
          >
            <td>{{ vendor.name || '—' }}</td>
            <td>{{ vendor.email || '—' }}</td>
            <td>{{ vendor.active ? 'Yes' : 'No' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                @click="edit(vendor)"
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
                Snapshot for {{ monthBounds.label }}
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
                      New this month
                    </div>
                    <div class="text-h5">
                      {{ stats.newThisMonth }}
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
                    v-else-if="!vendors.loading"
                    title="No vendors yet"
                    description="Counts and charts will appear once vendors are loaded."
                  />
                </div>

                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    This month & contact
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
                  v-model="filters.active"
                  label="Status"
                  :items="statusFilterItems"
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
              <AppAlert :errors="vendors.errors" />
              <VForm @submit.prevent="submit">
                <BaseInput
                  v-model="form.name"
                  label="Name"
                  :error="vendors.errors.name"
                />
                <BaseInput
                  v-model="form.email"
                  label="Email"
                  type="email"
                  :error="vendors.errors.email"
                />
                <BaseInput
                  v-model="form.password"
                  :label="editingId ? 'Password (leave blank to keep)' : 'Password'"
                  type="password"
                  :error="vendors.errors.password"
                />
                <BaseCheckbox
                  v-model="form.active"
                  label="Active"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
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
