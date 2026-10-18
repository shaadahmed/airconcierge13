<script setup>
import {
  emptyOwnerBlockForm,
  formatMoney,
  nightsBetween,
  ownerBlockFormFromRecord,
} from '@/constants/ownerBlocks'
import {
  BOOKING_STATUS_OWNER_BLOCK,
  OWNER_BLOCK_CLEANING_CHART_COLORS,
} from '@/constants/uiColors'
import { MOCK_REGIONS } from '@/mocks/regions'
import { MOCK_PROPERTIES } from '@/mocks/properties'
import {
  ownerBlockAddress,
  ownerBlockExitCleaningLabel,
  ownerBlockHasPostStayCleaning,
  ownerBlockLocation,
  ownerBlockPropertyTitle,
} from '@/mocks/ownerBlocks'

const route = useRoute()
const ownerBlocks = useOwnerBlocksStore()
const properties = usePropertiesStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'detail' | 'create' | 'edit'>} */
const panelMode = ref('dashboard')
const form = reactive(emptyOwnerBlockForm('create'))

const currentYear = new Date().getFullYear()
const currentMonth = new Date().getMonth() + 1

const defaultFilters = () => ({
  view_deleted: false,
  region_id: null,
  subregion_id: null,
  property_id: null,
  year: currentYear,
  month: currentMonth,
  start_date: '',
  end_date: '',
})

const filters = reactive(defaultFilters())
const listSearch = ref('')
const selectedId = ref(null)
const confirmDelete = reactive({ open: false, id: null })
const panelBodyRef = ref(null)
const exportNotice = ref('')

const allRows = computed(() => {
  const data = ownerBlocks.data?.data || ownerBlocks.data || []

  return Array.isArray(data) ? data : []
})

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

const yearFilterItems = [
  { title: 'All', value: 0 },
  ...Array.from({ length: currentYear - 2014 + 2 }, (_, index) => {
    const year = 2014 + index

    return { title: String(year), value: year }
  }).reverse(),
]

const monthFilterItems = [
  { title: 'All', value: 0 },
  ...monthNames.map((name, index) => ({
    title: `${String(index + 1).padStart(2, '0')} ${name}`,
    value: index + 1,
  })),
]

const regionFilterItems = [
  { title: 'All', value: null },
  ...MOCK_REGIONS.map(region => ({
    title: region.name,
    value: region.id,
  })),
]

const subregionFilterItems = computed(() => {
  const region = MOCK_REGIONS.find(item => item.id === filters.region_id)
  const subregions = region?.subregions || []

  return [
    { title: 'All', value: null },
    ...subregions.map(subregion => ({
      title: subregion.name,
      value: subregion.id,
    })),
  ]
})

const propertyFilterItems = computed(() => {
  const rows = MOCK_PROPERTIES.filter(property => (
    (filters.region_id == null || Number(property.region_id) === Number(filters.region_id))
    && (filters.subregion_id == null || Number(property.subregion_id) === Number(filters.subregion_id))
  ))

  return [
    { title: 'All', value: null },
    ...rows.map(property => ({
      title: `${property.property_title} - ${property.street_address || ''}`.trim(),
      value: property.id,
    })),
  ]
})

const selectedMonthBounds = computed(() => {
  const year = Number(filters.year) > 0 ? Number(filters.year) : currentYear
  const month = Number(filters.month) > 0 ? Number(filters.month) : currentMonth
  const start = new Date(year, month - 1, 1)
  const end = new Date(year, month, 0, 23, 59, 59, 999)

  return {
    year,
    month,
    start,
    end,
    label: start.toLocaleString(undefined, { month: 'long', year: 'numeric' }),
  }
})

const parseDate = value => {
  if (!value)
    return null

  const date = new Date(value)

  return Number.isNaN(date.getTime()) ? null : date
}

const formatDate = value => {
  const date = parseDate(value)
  if (!date)
    return '—'

  return date.toLocaleDateString()
}

const nightCount = block => {
  if (block?.no_of_nights != null && Number(block.no_of_nights) > 0)
    return Number(block.no_of_nights)

  return nightsBetween(block?.reservation_start_date, block?.reservation_end_date)
}

const matchesFilters = block => {
  if (!filters.view_deleted && block.deleted_at)
    return false
  if (filters.view_deleted && !block.deleted_at)
    return false

  if (filters.region_id != null && Number(block.region_id) !== Number(filters.region_id))
    return false
  if (filters.subregion_id != null && Number(block.subregion_id) !== Number(filters.subregion_id))
    return false
  if (filters.property_id != null && Number(block.property_id) !== Number(filters.property_id))
    return false

  if (Number(filters.year) > 0) {
    const year = Number(block.year) || parseDate(block.reservation_start_date)?.getFullYear()
    if (year !== Number(filters.year))
      return false
  }

  if (Number(filters.month) > 0) {
    const month = Number(block.month) || ((parseDate(block.reservation_start_date)?.getMonth() ?? -1) + 1)
    if (month !== Number(filters.month))
      return false
  }

  if (filters.start_date) {
    const start = parseDate(block.reservation_start_date)
    const filterStart = parseDate(filters.start_date)
    if (!start || !filterStart || start < filterStart)
      return false
  }

  if (filters.end_date) {
    const end = parseDate(block.reservation_end_date)
    const filterEnd = parseDate(filters.end_date)
    if (!end || !filterEnd || end > filterEnd)
      return false
  }

  return true
}

const rows = computed(() => {
  const query = listSearch.value.trim().toLowerCase()

  return allRows.value.filter(block => {
    if (!matchesFilters(block))
      return false

    if (!query)
      return true

    const haystack = [
      ownerBlockPropertyTitle(block),
      ownerBlockAddress(block),
      ownerBlockLocation(block),
      block.booking_notes,
      block.first_name,
      block.last_name,
      block.email,
    ].join(' ').toLowerCase()

    return haystack.includes(query)
  })
})

const stats = computed(() => {
  const list = rows.value
  const totalNights = list.reduce((sum, block) => sum + (nightCount(block) || 0), 0)
  const totalCleaning = list.reduce((sum, block) => sum + (Number(block.payment_of_cleaners) || 0), 0)
  const withCleaning = list.filter(block => ownerBlockHasPostStayCleaning(block)).length
  const withoutCleaning = list.length - withCleaning

  return {
    total: list.length,
    totalNights,
    totalCleaning,
    withCleaning,
    withoutCleaning,
    chartTotal: list.length,
  }
})

const statusChartSeries = computed(() => [
  stats.value.withCleaning,
  stats.value.withoutCleaning,
])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Post-stay cleaning', 'No post-stay cleaning'],
  colors: [...OWNER_BLOCK_CLEANING_CHART_COLORS],
  legend: {
    position: 'right',
    fontSize: '12px',
    offsetY: 24,
    markers: { width: 10, height: 10, radius: 10 },
    itemMargin: { vertical: 6 },
  },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      offsetX: -8,
      donut: {
        size: '68%',
        labels: {
          show: true,
          // Light series fills make ApexCharts' default name color unreadable on white.
          name: {
            show: true,
            fontSize: '13px',
            color: '#22303E',
          },
          value: {
            show: true,
            fontSize: '22px',
            fontWeight: 600,
            color: '#22303E',
            formatter: value => String(value),
          },
          total: {
            show: true,
            label: 'Blocks',
            fontSize: '13px',
            color: '#6c757d',
            formatter: () => String(stats.value.chartTotal),
          },
        },
      },
    },
  },
  stroke: { width: 0 },
  states: {
    hover: { filter: { type: 'none' } },
    active: { filter: { type: 'none' } },
  },
  // Light palette segments must not paint the tooltip; white text on light fill is unreadable.
  tooltip: {
    theme: 'light',
    fillSeriesColor: false,
    y: { formatter: value => `${value} blocks` },
  },
}))

const selectedBlock = computed(() => {
  if (selectedId.value == null)
    return null

  return allRows.value.find(block => String(block.id) === String(selectedId.value)) || null
})

/** Detail and edit keep the summary/form panel on the right. */
const isDetailLayout = computed(() => (
  (panelMode.value === 'detail' || panelMode.value === 'edit')
  && Boolean(selectedBlock.value)
))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Advance search'
  if (panelMode.value === 'create')
    return 'Add new block'
  if (panelMode.value === 'edit')
    return 'Update block'
  if (panelMode.value === 'detail' && selectedBlock.value)
    return 'Owner block'

  return 'Blocks overview'
})

const propertyOptions = computed(() => {
  const data = properties.data?.data || properties.data || []
  const list = Array.isArray(data) && data.length ? data : MOCK_PROPERTIES

  const options = [
    { title: 'Select', value: null },
    ...list.map(property => ({
      title: `${property.property_title} - ${property.street_address || ''}`.trim(),
      value: property.id,
      property,
    })),
  ]

  const currentId = form.property_id
    ?? selectedBlock.value?.property_id
    ?? selectedBlock.value?.property?.id

  if (currentId != null && !options.some(option => String(option.value) === String(currentId))) {
    options.splice(1, 0, {
      title: ownerBlockPropertyTitle(selectedBlock.value) || `Property #${currentId}`,
      value: currentId,
      property: selectedBlock.value?.property || null,
    })
  }

  return options
})

const resetPanelScroll = () => {
  nextTick(() => {
    const el = panelBodyRef.value?.$el ?? panelBodyRef.value
    if (el && typeof el.scrollTop === 'number')
      el.scrollTop = 0
  })
}

const resetCreateForm = () => {
  Object.assign(form, emptyOwnerBlockForm('create'))
  ownerBlocks.errors = {}
}

const openCreatePanel = () => {
  resetCreateForm()
  selectedId.value = null
  panelMode.value = 'create'
  resetPanelScroll()
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

const selectBlock = block => {
  selectedId.value = block.id
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

const openEditPanel = id => {
  const block = allRows.value.find(row => String(row.id) === String(id))
  if (!block)
    return

  selectedId.value = block.id
  Object.assign(form, ownerBlockFormFromRecord(block))
  ownerBlocks.errors = {}
  panelMode.value = 'edit'
  resetPanelScroll()
}

const cancelEdit = () => {
  ownerBlocks.errors = {}
  panelMode.value = selectedId.value != null ? 'detail' : 'dashboard'
  resetPanelScroll()
}

const onFormUpdate = value => {
  Object.assign(form, value)
}

const submitCreate = async () => {
  try {
    const selected = propertyOptions.value.find(
      option => String(option.value) === String(form.property_id),
    )
    const property = selected?.property

    const payload = {
      ...form,
      property_title: property?.property_title || selected?.title,
      street_address: property?.street_address || '',
      location: property
        ? `${property.subregion?.name || property.city?.name || '—'}, ${property.city?.state?.code || ''}`.trim()
        : '—',
      region_id: property?.region_id ?? null,
      subregion_id: property?.subregion_id ?? null,
      property: property
        ? {
            id: property.id,
            property_title: property.property_title,
            street_address: property.street_address,
            property_image_url: property.property_image_url,
            region_id: property.region_id,
            subregion_id: property.subregion_id,
          }
        : null,
    }

    const block = await ownerBlocks.create(payload)

    resetCreateForm()
    selectedId.value = block.id
    panelMode.value = 'detail'
    resetPanelScroll()
    if (!ownerBlocks.usingMocks)
      await ownerBlocks.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

const clearFilters = () => {
  Object.assign(filters, defaultFilters())
}

const applyFilters = () => {
  panelMode.value = selectedId.value ? 'detail' : 'dashboard'
}

const submitEdit = async () => {
  if (selectedId.value == null)
    return

  try {
    await ownerBlocks.save(selectedId.value, { ...form })
    panelMode.value = 'detail'
    resetPanelScroll()
    if (!ownerBlocks.usingMocks)
      await ownerBlocks.load()
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

const askDelete = id => {
  confirmDelete.open = true
  confirmDelete.id = id
}

const onDelete = async () => {
  try {
    const deletedId = confirmDelete.id
    await ownerBlocks.remove(deletedId)
    confirmDelete.open = false
    if (String(selectedId.value) === String(deletedId))
      closeDetail()
    if (!ownerBlocks.usingMocks)
      await ownerBlocks.load()
  }
  catch {
    // Store exposes errors.
  }
}

const downloadCsv = (filename, list) => {
  const columns = [
    { label: 'Property Address', value: row => `${ownerBlockPropertyTitle(row)} - ${ownerBlockAddress(row)}` },
    { label: 'Start of Reservation', value: row => row.reservation_start_date },
    { label: 'End date of Reservation', value: row => row.reservation_end_date },
    { label: 'No. of nights', value: row => nightCount(row) ?? '' },
    { label: 'Cleaning Fee Paid to Cleaners', value: row => row.payment_of_cleaners },
    { label: 'Post owner stay cleaning', value: row => ownerBlockExitCleaningLabel(row) },
    { label: 'Notes', value: row => row.booking_notes || '' },
    { label: 'Creation Date', value: row => row.created_at || '' },
    { label: 'Latest Action', value: row => row.latest_log || '' },
  ]
  const escape = value => `"${String(value ?? '').replaceAll('"', '""')}"`
  const header = columns.map(column => escape(column.label)).join(',')
  const body = list.map(row => columns.map(column => escape(column.value(row))).join(',')).join('\n')
  const blob = new Blob([`${header}\n${body}`], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')

  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)
}

const exportBlocks = () => {
  downloadCsv('owner-blocks.csv', rows.value)
  exportNotice.value = 'Exported filtered owner blocks as CSV (Excel-compatible).'
}

watch(() => filters.region_id, () => {
  filters.subregion_id = null
})

onMounted(async () => {
  await Promise.all([
    ownerBlocks.load(),
    properties.load(),
  ])

  if (route.query.create === '1' || route.query.create === 'true') {
    openCreatePanel()

    return
  }

  if (route.query.edit != null && route.query.edit !== '')
    openEditPanel(route.query.edit)
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div class="owner-blocks-page">
    <PageHeader
      class="flex-shrink-0"
      title="Owner blocks"
      subtitle="Calendar blocks requested by property owners"
    />

    <VAlert
      v-if="ownerBlocks.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4 owner-blocks-page__alert"
    >
      Showing UI preview data until the owner blocks API is connected.
    </VAlert>

    <VAlert
      v-if="exportNotice"
      type="success"
      variant="tonal"
      density="compact"
      class="mb-4"
      closable
      @click:close="exportNotice = ''"
    >
      {{ exportNotice }}
    </VAlert>

    <div
      class="owner-blocks-page__stage"
      :class="{ 'owner-blocks-page__stage--detail': isDetailLayout }"
    >
      <div class="owner-blocks-page__col owner-blocks-page__col--side">
        <VCard class="owner-block-panel-card">
          <VCardItem class="flex-shrink-0">
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template #append>
              <div class="d-flex flex-wrap gap-2 align-center">
                <BaseButton
                  v-if="!isDetailLayout"
                  size="small"
                  color="primary-light"
                  variant="flat"
                  label="Filter"
                  prepend-icon="bx-filter-alt"
                  @click="openPanel('filter')"
                />
                <BaseButton
                  v-if="!isDetailLayout"
                  size="small"
                  color="primary"
                  label="Create"
                  prepend-icon="bx-plus"
                  @click="openPanel('create')"
                />
                <VBtn
                  v-if="panelMode === 'edit'"
                  icon
                  variant="text"
                  size="small"
                  aria-label="Close edit owner block"
                  @click="cancelEdit"
                >
                  <VIcon icon="bx-x" />
                </VBtn>
                <VBtn
                  v-else-if="panelMode === 'detail' && selectedBlock"
                  icon
                  variant="text"
                  size="small"
                  aria-label="Close block details"
                  @click="closeDetail"
                >
                  <VIcon icon="bx-x" />
                </VBtn>
                <VBtn
                  v-else-if="panelMode === 'create'"
                  icon
                  variant="text"
                  size="small"
                  aria-label="Close create owner block"
                  @click="cancelCreate"
                >
                  <VIcon icon="bx-x" />
                </VBtn>
              </div>
            </template>
          </VCardItem>

          <VCardText
            ref="panelBodyRef"
            class="owner-block-panel-body"
          >
            <Transition
              name="owner-block-panel-fade"
              mode="out-in"
            >
              <div
                v-if="panelMode === 'edit' && selectedBlock"
                :key="`edit-${selectedBlock.id}`"
              >
                <AppAlert :errors="ownerBlocks.errors" />
                <OwnerBlockForm
                  :model-value="form"
                  mode="edit"
                  submit-label="Update"
                  :property-options="propertyOptions"
                  :loading="ownerBlocks.loading"
                  :errors="ownerBlocks.errors"
                  @update:model-value="onFormUpdate"
                  @submit="submitEdit"
                  @cancel="cancelEdit"
                />
              </div>

              <div
                v-else-if="panelMode === 'detail' && selectedBlock"
                :key="`detail-${selectedBlock.id}`"
                class="owner-block-detail-minimal"
              >
                <div class="d-flex align-center gap-3 mb-4">
                  <VAvatar
                    size="56"
                    rounded="lg"
                    :color="BOOKING_STATUS_OWNER_BLOCK"
                    variant="tonal"
                  >
                    <VImg
                      v-if="selectedBlock.property?.property_image_url"
                      :src="selectedBlock.property.property_image_url"
                      cover
                    />
                    <VIcon
                      v-else
                      icon="bx-calendar-x"
                      size="26"
                    />
                  </VAvatar>
                  <div class="min-w-0">
                    <div class="d-flex align-center flex-wrap gap-2 mb-1">
                      <h2 class="text-h6 mb-0 text-truncate">
                        {{ ownerBlockPropertyTitle(selectedBlock) }}
                      </h2>
                      <VChip
                        size="x-small"
                        :color="BOOKING_STATUS_OWNER_BLOCK"
                        label
                      >
                        Owner Block
                      </VChip>
                    </div>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                      {{ ownerBlockAddress(selectedBlock) }}
                    </p>
                  </div>
                </div>

                <div class="d-flex flex-column gap-2 mb-5">
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-calendar"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>
                      {{ formatDate(selectedBlock.reservation_start_date) }}
                      –
                      {{ formatDate(selectedBlock.reservation_end_date) }}
                      <template v-if="nightCount(selectedBlock)">
                        ({{ nightCount(selectedBlock) }} nights)
                      </template>
                    </span>
                  </div>
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-dollar"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>
                      Cleaning fee:
                      {{ formatMoney(selectedBlock.payment_of_cleaners) }}
                    </span>
                  </div>
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-check-circle"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>
                      Post owner stay cleaning:
                      {{ ownerBlockExitCleaningLabel(selectedBlock) }}
                    </span>
                  </div>
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-message-detail"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedBlock.booking_notes || 'No notes' }}</span>
                  </div>
                  <div class="d-flex align-center gap-2 text-body-2">
                    <VIcon
                      icon="bx-time"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>Created {{ formatDate(selectedBlock.created_at) }}</span>
                  </div>
                  <div
                    v-if="selectedBlock.latest_log"
                    class="d-flex align-center gap-2 text-body-2"
                  >
                    <VIcon
                      icon="bx-history"
                      size="18"
                      class="text-medium-emphasis"
                    />
                    <span>{{ selectedBlock.latest_log }}</span>
                  </div>
                </div>

                <VRow dense>
                  <VCol cols="6">
                    <BaseButton
                      color="primary-light"
                      variant="flat"
                      block
                      label="Edit block"
                      prepend-icon="bx-edit"
                      @click="openEditPanel(selectedBlock.id)"
                    />
                  </VCol>
                  <VCol cols="6">
                    <BaseButton
                      block
                      label="Delete"
                      prepend-icon="bx-trash"
                      @click="askDelete(selectedBlock.id)"
                    />
                  </VCol>
                </VRow>
              </div>

              <div
                v-else-if="panelMode === 'create'"
                key="create"
              >
                <AppAlert :errors="ownerBlocks.errors" />
                <OwnerBlockForm
                  :model-value="form"
                  mode="create"
                  submit-label="Save block"
                  :property-options="propertyOptions"
                  :loading="ownerBlocks.loading"
                  :errors="ownerBlocks.errors"
                  @update:model-value="onFormUpdate"
                  @submit="submitCreate"
                  @cancel="cancelCreate"
                />
              </div>

              <div
                v-else-if="panelMode === 'filter'"
                key="filter"
              >
                <VForm @submit.prevent="applyFilters">
                  <div class="d-flex flex-wrap gap-x-4 gap-y-2 mb-4">
                    <BaseCheckbox
                      v-model="filters.view_deleted"
                      label="View Deleted Entries"
                      hide-details
                    />
                  </div>

                  <VRow>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.region_id"
                        label="City / Region"
                        :items="regionFilterItems"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.subregion_id"
                        label="Subregion"
                        :items="subregionFilterItems"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.property_id"
                        label="Property"
                        :items="propertyFilterItems"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.year"
                        label="Year"
                        :items="yearFilterItems"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseSelect
                        v-model="filters.month"
                        label="Month"
                        :items="monthFilterItems"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseInput
                        v-model="filters.start_date"
                        label="Start of Reservation"
                        type="date"
                      />
                    </VCol>
                    <VCol
                      cols="12"
                      sm="6"
                    >
                      <BaseInput
                        v-model="filters.end_date"
                        label="End of Reservation"
                        type="date"
                      />
                    </VCol>
                  </VRow>

                  <div class="d-flex flex-wrap gap-2 mt-4">
                    <BaseButton
                      type="button"
                      color="primary-light"
                      variant="flat"
                      label="Clear All"
                      @click="clearFilters"
                    />
                    <BaseButton
                      type="submit"
                      color="primary"
                      label="Search"
                    />
                    <BaseButton
                      type="button"
                      color="primary-light"
                      variant="flat"
                      label="Back to overview"
                      @click="panelMode = selectedId ? 'detail' : 'dashboard'"
                    />
                  </div>
                </VForm>
              </div>

              <div
                v-else
                key="dashboard"
              >
                <p class="text-body-2 text-medium-emphasis mb-4">
                  Snapshot for {{ selectedMonthBounds.label }}
                </p>

                <VRow
                  dense
                  class="mb-2"
                >
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--secondary pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Owner blocks
                      </div>
                      <div class="text-h5">
                        {{ stats.total }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--info pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Total nights
                      </div>
                      <div class="text-h5">
                        {{ stats.totalNights }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Cleaning fees
                      </div>
                      <div class="text-h5 text-body-1">
                        {{ formatMoney(stats.totalCleaning) }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="entity-stat-tile entity-stat-tile--danger pa-3 rounded">
                      <div class="text-caption text-medium-emphasis">
                        Need post-stay clean
                      </div>
                      <div class="text-h5">
                        {{ stats.withCleaning }}
                      </div>
                    </div>
                  </VCol>
                </VRow>

                <ClientOnly>
                  <div class="mt-4">
                    <div class="text-subtitle-2 mb-2">
                      Cleaning mix
                    </div>
                    <VueApexCharts
                      v-if="stats.chartTotal > 0"
                      type="donut"
                      height="220"
                      :options="statusChartOptions"
                      :series="statusChartSeries"
                    />
                    <EmptyState
                      v-else-if="!ownerBlocks.loading"
                      title="No owner blocks yet"
                      description="Counts and charts will appear once blocks are loaded."
                    />
                  </div>
                </ClientOnly>

                <VAlert
                  type="info"
                  variant="tonal"
                  density="compact"
                  class="mt-4"
                >
                  Select a block to open a summary on the right. Use Create to add a block in this panel.
                </VAlert>
              </div>
            </Transition>
          </VCardText>
        </VCard>
      </div>

      <div class="owner-blocks-page__col owner-blocks-page__col--list">
        <VCard class="owner-block-list-card">
          <VProgressLinear
            v-if="ownerBlocks.loading"
            indeterminate
            class="flex-shrink-0"
          />

          <VCardItem class="flex-shrink-0">
            <VCardTitle>Blocks list</VCardTitle>
            <template #append>
              <BaseButton
                size="small"
                color="primary-light"
                variant="flat"
                label="Export"
                prepend-icon="bx-export"
                @click="exportBlocks"
              />
            </template>
          </VCardItem>

          <VCardText class="pb-2 flex-shrink-0">
            <BaseInput
              v-model="listSearch"
              placeholder="Search properties, notes, owners..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <div class="owner-block-list">
            <button
              v-for="block in rows"
              :key="block.id"
              type="button"
              class="owner-block-list-item"
              :class="{ 'owner-block-list-item--selected': String(selectedId) === String(block.id) }"
              @click="selectBlock(block)"
            >
              <span
                class="owner-block-list-item__flag"
                :style="{ background: BOOKING_STATUS_OWNER_BLOCK }"
              >
                Owner Block
              </span>

              <VChip
                size="x-small"
                :color="ownerBlockHasPostStayCleaning(block) ? 'success' : 'secondary'"
                variant="tonal"
                label
                class="owner-block-list-item__status"
              >
                Clean: {{ ownerBlockExitCleaningLabel(block) }}
              </VChip>

              <div class="owner-block-list-item__content">
                <div class="owner-block-list-item__property">
                  <VAvatar
                    size="52"
                    rounded="lg"
                    class="owner-block-list-item__thumb"
                    :color="BOOKING_STATUS_OWNER_BLOCK"
                    variant="tonal"
                  >
                    <VImg
                      v-if="block.property?.property_image_url"
                      :src="block.property.property_image_url"
                      cover
                    />
                    <VIcon
                      v-else
                      icon="bx-calendar-x"
                      size="22"
                    />
                  </VAvatar>

                  <div class="owner-block-list-item__property-text">
                    <div class="owner-block-list-item__title text-truncate">
                      {{ ownerBlockPropertyTitle(block) }}
                    </div>
                    <div class="owner-block-list-item__subtitle text-truncate">
                      {{ ownerBlockAddress(block) }}
                    </div>
                  </div>
                </div>

                <div class="owner-block-list-item__meta">
                  <div class="owner-block-list-item__dates">
                    {{ formatDate(block.reservation_start_date) }}
                    –
                    {{ formatDate(block.reservation_end_date) }}
                  </div>
                  <div class="owner-block-list-item__nights text-truncate">
                    {{ nightCount(block) ?? '—' }} nights
                  </div>
                  <div class="owner-block-list-item__notes text-truncate">
                    {{ block.booking_notes || 'No notes' }}
                  </div>
                </div>

                <div class="owner-block-list-item__money">
                  <div class="owner-block-list-item__money-line">
                    <span class="owner-block-list-item__money-label">Cleaning fee:</span>
                    <span class="owner-block-list-item__money-value">{{ formatMoney(block.payment_of_cleaners) }}</span>
                  </div>
                  <div class="owner-block-list-item__money-line">
                    <span class="owner-block-list-item__money-label">Created:</span>
                    <span class="owner-block-list-item__money-value">{{ formatDate(block.created_at) }}</span>
                  </div>
                </div>
              </div>
            </button>

            <EmptyState
              v-if="!ownerBlocks.loading && rows.length === 0"
              title="No owner blocks found"
              :description="allRows.length && rows.length === 0 ? 'No blocks match the current filters.' : ''"
            />
          </div>

          <div
            v-if="rows.length"
            class="owner-block-list-footer flex-shrink-0"
          >
            <div class="text-body-2">
              <strong>Total</strong>
              · {{ stats.totalNights }} nights
              · {{ formatMoney(stats.totalCleaning) }} cleaning fees
            </div>
          </div>
        </VCard>
      </div>
    </div>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete owner block?"
      message="I have read and agreed to the terms in full listed at http://www.airconcierge.net/terms-of-use/. This removes the block from the list (UI preview only for now)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="ownerBlocks.loading"
      @confirm="onDelete"
    />
  </div>
</template>

<style scoped>
.owner-blocks-page {
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.owner-blocks-page__alert {
  flex: 0 0 auto;
}

.owner-blocks-page__stage {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  min-height: 0;
}

@media (min-width: 960px) {
  .owner-blocks-page {
    height: calc(100dvh - 64px - 3rem);
    overflow: hidden;
  }

  .owner-blocks-page__stage {
    position: relative;
    flex: 1 1 0;
    flex-direction: row;
    gap: 0;
    min-height: 0;
    overflow: hidden;
  }

  .owner-blocks-page__col {
    position: absolute;
    top: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    min-height: 0;
    max-height: 100%;
    transition:
      left 0.45s cubic-bezier(0.22, 1, 0.36, 1),
      right 0.45s cubic-bezier(0.22, 1, 0.36, 1),
      padding 0.45s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: left;
  }

  .owner-block-list-card,
  .owner-block-panel-card {
    flex: 1 1 auto;
    height: 100%;
    min-height: 0;
    max-height: 100%;
  }

  .owner-blocks-page__col--side {
    width: calc(41.6667% - 0.5rem);
    left: 0;
  }

  .owner-blocks-page__col--list {
    width: calc(58.3333% - 0.5rem);
    left: calc(41.6667% + 0.5rem);
  }

  .owner-blocks-page__stage--detail .owner-blocks-page__col--list {
    left: 0;
  }

  .owner-blocks-page__stage--detail .owner-blocks-page__col--side {
    left: calc(58.3333% + 0.5rem);
  }
}

@media (min-width: 960px) and (prefers-reduced-motion: reduce) {
  .owner-blocks-page__col {
    transition: none;
  }
}

.owner-block-list-card,
.owner-block-panel-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.owner-block-panel-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  /* Extra room so the first outlined label is not clipped by overflow. */
  padding-block-start: 1.25rem;
}

.owner-block-list {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  padding: 0 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.owner-block-list-footer {
  padding: 12px 16px;
  border-top: 1px solid rgba(75, 70, 92, 0.12);
}

.owner-block-list-item {
  position: relative;
  display: block;
  flex-shrink: 0;
  width: 100%;
  padding: 2.25rem 1rem 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  text-align: left;
  cursor: pointer;
  overflow: hidden;
  transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.owner-block-list-item:hover {
  background: rgba(108, 117, 125, 0.06);
  border-color: rgba(108, 117, 125, 0.35);
}

.owner-block-list-item--selected {
  background: rgba(108, 117, 125, 0.12);
  border-color: rgba(108, 117, 125, 0.5);
  box-shadow: 0 0 0 1px rgba(108, 117, 125, 0.14);
}

.owner-block-list-item__flag {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
  max-width: calc(100% - 7rem);
  padding: 4px 14px 5px 12px;
  border-radius: 12px 0 10px 0;
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  line-height: 1.3;
  text-transform: uppercase;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background: #6c757d;
}

.owner-block-list-item__status {
  position: absolute;
  top: 8px;
  right: 10px;
  z-index: 1;
}

.owner-block-list-item__content {
  display: flex;
  align-items: center;
  gap: 1rem 1.25rem;
}

.owner-block-list-item__property {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
  flex: 1.2 1 0;
}

.owner-block-list-item__property-text {
  min-width: 0;
}

.owner-block-list-item__title {
  font-weight: 600;
  font-size: 0.9375rem;
  line-height: 1.3;
}

.owner-block-list-item__subtitle {
  color: rgba(75, 70, 92, 0.7);
  font-size: 0.8125rem;
}

.owner-block-list-item__meta {
  min-width: 0;
  flex: 1 1 0;
}

.owner-block-list-item__dates {
  font-size: 0.8125rem;
  font-weight: 600;
}

.owner-block-list-item__nights,
.owner-block-list-item__notes {
  color: rgba(75, 70, 92, 0.7);
  font-size: 0.75rem;
}

.owner-block-list-item__money {
  flex: 0 0 auto;
  text-align: right;
}

.owner-block-list-item__money-line {
  display: flex;
  justify-content: flex-end;
  gap: 0.35rem;
  font-size: 0.75rem;
  white-space: nowrap;
}

.owner-block-list-item__money-label {
  color: rgba(75, 70, 92, 0.65);
}

.owner-block-list-item__money-value {
  font-weight: 600;
}

.entity-stat-tile {
  border: 1px solid rgba(75, 70, 92, 0.1);
  background: rgba(75, 70, 92, 0.03);
}

.entity-stat-tile--success {
  border-color: rgba(22, 163, 74, 0.25);
  background: rgba(22, 163, 74, 0.08);
}

.entity-stat-tile--info {
  border-color: rgba(104, 101, 255, 0.25);
  background: rgba(104, 101, 255, 0.08);
}

.entity-stat-tile--secondary {
  border-color: rgba(108, 117, 125, 0.28);
  background: rgba(108, 117, 125, 0.1);
}

.entity-stat-tile--danger {
  border-color: rgba(234, 84, 85, 0.25);
  background: rgba(234, 84, 85, 0.08);
}

.owner-block-panel-fade-enter-active,
.owner-block-panel-fade-leave-active {
  transition: opacity 0.18s ease;
}

.owner-block-panel-fade-enter-from,
.owner-block-panel-fade-leave-to {
  opacity: 0;
}

@media (max-width: 959px) {
  .owner-block-list-item__content {
    flex-direction: column;
    align-items: stretch;
  }

  .owner-block-list-item__money {
    text-align: left;
  }

  .owner-block-list-item__money-line {
    justify-content: flex-start;
  }
}
</style>
