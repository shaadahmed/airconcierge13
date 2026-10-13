<script setup>
import { MOCK_PROPERTIES } from '@/mocks/properties'
import {
  formatOwnerValue,
  formatRate,
  revenuePropertyItems,
} from '@/mocks/revenueSettings'

const settings = useRevenueSettingsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create' | 'detail'>} */
const panelMode = ref('dashboard')

const form = reactive({
  property_id: null,
  dynamic_pricing: false,
  base_rate: '',
  min_rate: '',
})

const filters = reactive({
  property_id: null,
})

const editingId = ref(null)
const selectedId = ref(null)
const listSearch = ref('')

const allRows = computed(() => {
  const data = settings.data?.data || settings.data || []

  return Array.isArray(data) ? data : []
})

const selectedRow = computed(() => (
  selectedId.value == null
    ? null
    : allRows.value.find(row => String(row.id) === String(selectedId.value)) || null
))

const propertyFilterItems = computed(() => {
  const fromRows = allRows.value
    .map(row => row.property || { id: row.property_id, property_title: `Property #${row.property_id}` })
    .filter(Boolean)

  return revenuePropertyItems(fromRows)
})

const formPropertyItems = computed(() => propertyFilterItems.value)

const editingRow = computed(() => (
  editingId.value == null
    ? null
    : allRows.value.find(row => String(row.id) === String(editingId.value)) || null
))

const ownerStated = computed(() => {
  const propertyId = form.property_id
  if (!propertyId)
    return { min_nightly_rate: null, min_nights_per_booking: null }

  const fromRow = allRows.value.find(row => String(row.property_id) === String(propertyId))
  if (fromRow) {
    return {
      min_nightly_rate: fromRow.min_nightly_rate,
      min_nights_per_booking: fromRow.min_nights_per_booking,
    }
  }

  const fromProperty = MOCK_PROPERTIES.find(item => String(item.id) === String(propertyId))?.revenue_settings

  return {
    min_nightly_rate: fromProperty?.min_nightly_rate ?? null,
    min_nights_per_booking: fromProperty?.min_nights_per_booking ?? null,
  }
})

const rows = computed(() => {
  const propertyQuery = filters.property_id
  const search = listSearch.value.trim().toLowerCase()

  return allRows.value.filter(row => {
    if (propertyQuery != null && propertyQuery !== '' && String(row.property_id) !== String(propertyQuery))
      return false

    if (search) {
      const title = (row.property?.property_title || String(row.property_id)).toLowerCase()
      if (!title.includes(search))
        return false
    }

    return true
  })
})

const stats = computed(() => {
  const list = allRows.value
  const dynamicOn = list.filter(row => Boolean(Number(row.dynamic_pricing)))
  const withBase = list.filter(row => row.base_rate != null && row.base_rate !== '')
  const withMin = list.filter(row => row.min_rate != null && row.min_rate !== '')

  return {
    total: list.length,
    dynamicOn: dynamicOn.length,
    dynamicOff: list.length - dynamicOn.length,
    withBase: withBase.length,
    withMin: withMin.length,
  }
})

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter revenue settings'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit revenue setting' : 'Create revenue setting'
  if (panelMode.value === 'detail' && selectedRow.value)
    return selectedRow.value.property?.property_title || `Property #${selectedRow.value.property_id}`

  return 'Revenue overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = selectedRow.value ? 'detail' : 'dashboard'

    return
  }

  if (mode === 'create' && panelMode.value !== 'create') {
    editingId.value = null
    Object.assign(form, {
      property_id: null,
      dynamic_pricing: false,
      base_rate: '',
      min_rate: '',
    })
  }

  panelMode.value = mode
}

const clearFilters = () => {
  filters.property_id = null
  listSearch.value = ''
}

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    property_id: null,
    dynamic_pricing: false,
    base_rate: '',
    min_rate: '',
  })
}

const selectRow = row => {
  selectedId.value = row.id
  panelMode.value = 'detail'
}

const closeDetail = () => {
  selectedId.value = null
  panelMode.value = 'dashboard'
}

const edit = row => {
  const target = row || selectedRow.value
  if (!target)
    return

  editingId.value = target.id
  selectedId.value = target.id
  Object.assign(form, {
    property_id: target.property_id || null,
    dynamic_pricing: Boolean(Number(target.dynamic_pricing)),
    base_rate: target.base_rate ?? '',
    min_rate: target.min_rate ?? '',
  })
  panelMode.value = 'create'
}

const submit = async () => {
  try {
    const payload = {
      property_id: Number(form.property_id),
      dynamic_pricing: form.dynamic_pricing ? 1 : 0,
      base_rate: form.base_rate === '' ? null : Number(form.base_rate),
      min_rate: form.min_rate === '' ? null : Number(form.min_rate),
      min_nightly_rate: ownerStated.value.min_nightly_rate,
      min_nights_per_booking: ownerStated.value.min_nights_per_booking,
    }

    let saved
    if (editingId.value)
      saved = await settings.update(editingId.value, payload)
    else
      saved = await settings.create(payload)

    selectedId.value = saved?.id || editingId.value
    resetForm()
    panelMode.value = selectedId.value ? 'detail' : 'dashboard'
    if (!settings.usingMocks)
      await settings.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

const cancelForm = () => {
  resetForm()
  panelMode.value = selectedId.value ? 'detail' : 'dashboard'
}

onMounted(() => settings.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div class="revenue-page">
    <PageHeader
      class="flex-shrink-0"
      title="Revenue Settings"
      subtitle="Per-property KYC / revenue configuration"
    >
      <template #actions>
        <BaseButton
          color="primary"
          label="Create setting"
          prepend-icon="bx-plus"
          @click="openPanel('create')"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="settings.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4 revenue-page__alert"
    >
      Showing UI preview data until the revenue settings API is connected.
    </VAlert>

    <div class="revenue-page__stage">
      <!-- List: left 60% — settings for each property -->
      <div class="revenue-page__col revenue-page__col--list">
        <VCard class="revenue-list-card">
          <VCardItem class="flex-shrink-0">
            <VCardTitle>Property revenue settings</VCardTitle>
          </VCardItem>

          <VCardText class="revenue-list-card__search flex-shrink-0">
            <BaseInput
              v-model="listSearch"
              placeholder="Search properties..."
              size="small"
              hide-details
              prepend-inner-icon="bx-search"
            />
          </VCardText>

          <VProgressLinear
            :active="settings.loading"
            :indeterminate="settings.loading"
            class="flex-shrink-0"
          />

          <div class="revenue-list">
            <button
              v-for="row in rows"
              :key="row.id"
              type="button"
              class="revenue-list-item"
              :class="{ 'revenue-list-item--selected': String(selectedId) === String(row.id) }"
              @click="selectRow(row)"
            >
              <div class="revenue-list-item__main">
                <div class="revenue-list-item__title text-truncate">
                  {{ row.property?.property_title || `Property #${row.property_id}` }}
                </div>
                <div class="revenue-list-item__rates">
                  <span>
                    <span class="revenue-list-item__rate-label">Base</span>
                    {{ formatRate(row.base_rate) }}
                  </span>
                  <span>
                    <span class="revenue-list-item__rate-label">Min</span>
                    {{ formatRate(row.min_rate) }}
                  </span>
                </div>
              </div>
              <VIcon
                icon="bx-chevron-right"
                size="20"
                class="revenue-list-item__chevron"
              />
            </button>

            <EmptyState
              v-if="!settings.loading && rows.length === 0"
              title="No revenue settings found"
              :description="allRows.length && rows.length === 0 ? 'No settings match the current filters.' : ''"
            />
          </div>
        </VCard>
      </div>

      <!-- Panel: right 40% — overview / detail / filter / create -->
      <div class="revenue-page__col revenue-page__col--side">
        <VCard class="revenue-panel-card">
          <VCardItem class="flex-shrink-0">
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
                <template v-if="panelMode === 'detail' && selectedRow">
                  <BaseButton
                    size="small"
                    color="primary"
                    label="Edit"
                    prepend-icon="bx-edit"
                    @click="edit(selectedRow)"
                  />
                  <VBtn
                    icon
                    variant="text"
                    size="small"
                    aria-label="Close details"
                    @click="closeDetail"
                  >
                    <VIcon icon="bx-x" />
                  </VBtn>
                </template>
              </div>
            </template>
          </VCardItem>

          <VCardText class="revenue-panel-body">
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of revenue configuration across properties
              </p>

              <VRow dense>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Dynamic on
                    </div>
                    <div class="text-h5">
                      {{ stats.dynamicOn }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Dynamic off
                    </div>
                    <div class="text-h5">
                      {{ stats.dynamicOff }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With base rate
                    </div>
                    <div class="text-h5">
                      {{ stats.withBase }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With min rate
                    </div>
                    <div class="text-h5">
                      {{ stats.withMin }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <p class="text-body-2 text-medium-emphasis mt-4 mb-0">
                Select a property on the left to view details, or use Filter / Create.
              </p>
            </div>

            <div v-else-if="panelMode === 'detail' && selectedRow">
              <div class="revenue-detail">
                <div class="revenue-detail__row">
                  <span class="revenue-detail__label">Property</span>
                  <span class="revenue-detail__value">
                    {{ selectedRow.property?.property_title || `Property #${selectedRow.property_id}` }}
                  </span>
                </div>
                <div class="revenue-detail__row">
                  <span class="revenue-detail__label">Dynamic pricing</span>
                  <VChip
                    size="small"
                    :color="Number(selectedRow.dynamic_pricing) ? 'success' : 'secondary'"
                    label
                  >
                    {{ Number(selectedRow.dynamic_pricing) ? 'On' : 'Off' }}
                  </VChip>
                </div>
                <div class="revenue-detail__row">
                  <span class="revenue-detail__label">Owner stated min nightly</span>
                  <span class="revenue-detail__value">{{ formatRate(selectedRow.min_nightly_rate) }}</span>
                </div>
                <div class="revenue-detail__row">
                  <span class="revenue-detail__label">Owner stated min nights</span>
                  <span class="revenue-detail__value">{{ formatOwnerValue(selectedRow.min_nights_per_booking) }}</span>
                </div>
                <div class="revenue-detail__row">
                  <span class="revenue-detail__label">Base rate</span>
                  <span class="revenue-detail__value">{{ formatRate(selectedRow.base_rate) }}</span>
                </div>
                <div
                  v-if="selectedRow.previous_base_rate != null"
                  class="revenue-detail__prev"
                >
                  Previous Value: {{ formatRate(selectedRow.previous_base_rate) }}
                </div>
                <div class="revenue-detail__row">
                  <span class="revenue-detail__label">Minimum rate</span>
                  <span class="revenue-detail__value">{{ formatRate(selectedRow.min_rate) }}</span>
                </div>
                <div
                  v-if="selectedRow.previous_min_rate != null"
                  class="revenue-detail__prev"
                >
                  Previous Value: {{ formatRate(selectedRow.previous_min_rate) }}
                </div>
              </div>
            </div>

            <div v-else-if="panelMode === 'filter'">
              <VForm @submit.prevent>
                <BaseSelect
                  v-model="filters.property_id"
                  label="Property"
                  :items="propertyFilterItems"
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
                    @click="panelMode = selectedRow ? 'detail' : 'dashboard'"
                  />
                </div>
              </VForm>
            </div>

            <div v-else-if="panelMode === 'create'">
              <AppAlert :errors="settings.errors" />
              <VForm @submit.prevent="submit">
                <BaseSelect
                  v-model="form.property_id"
                  label="Property"
                  :items="formPropertyItems"
                  :error="settings.errors.property_id"
                  :disabled="Boolean(editingId)"
                  class="mb-2"
                />

                <VRow dense class="mb-2">
                  <VCol cols="12">
                    <BaseInput
                      :model-value="formatRate(ownerStated.min_nightly_rate)"
                      label="Owner Stated Minimum Nightly Rate"
                      disabled
                      readonly
                    />
                  </VCol>
                  <VCol cols="12">
                    <BaseInput
                      :model-value="formatOwnerValue(ownerStated.min_nights_per_booking)"
                      label="Owner Stated Minimum Number Of Nights"
                      disabled
                      readonly
                    />
                  </VCol>
                </VRow>

                <BaseCheckbox
                  v-model="form.dynamic_pricing"
                  label="Dynamic Pricing"
                  hide-details
                  class="mb-3"
                />

                <BaseInput
                  v-model="form.base_rate"
                  label="Base Rate"
                  type="number"
                  placeholder="$"
                  :error="settings.errors.base_rate"
                  class="mb-1"
                />
                <div
                  v-if="editingRow?.previous_base_rate != null"
                  class="text-caption text-success mb-3"
                >
                  Previous Value: {{ formatRate(editingRow.previous_base_rate) }}
                </div>

                <BaseInput
                  v-model="form.min_rate"
                  label="Minimum Rate"
                  type="number"
                  placeholder="$"
                  :error="settings.errors.min_rate"
                  class="mb-1"
                />
                <div
                  v-if="editingRow?.previous_min_rate != null"
                  class="text-caption text-success mb-3"
                >
                  Previous Value: {{ formatRate(editingRow.previous_min_rate) }}
                </div>

                <div class="d-flex flex-wrap gap-2 mt-2 justify-end">
                  <BaseButton
                    type="button"
                    variant="tonal"
                    label="Cancel"
                    @click="cancelForm"
                  />
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Save' : 'Create setting'"
                    prepend-icon="bx-check"
                    :loading="settings.loading"
                  />
                </div>
              </VForm>
            </div>
          </VCardText>
        </VCard>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Navbar (64px) + layout-page-content padding-block (1.5rem × 2) */
.revenue-page {
  display: flex;
  flex-direction: column;
  min-height: 0;
}

/* Vuetify sets .v-alert { flex: 1 1 }; keep preview banner compact. */
.revenue-page__alert {
  flex: 0 0 auto;
}

.revenue-page__stage {
  display: grid;
  grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
  gap: 1rem;
  align-items: stretch;
  min-height: 0;
  width: 100%;
}

@media (min-width: 960px) {
  .revenue-page {
    height: calc(100dvh - 64px - 3rem);
    overflow: hidden;
  }

  .revenue-page__stage {
    flex: 1 1 0;
    min-height: 0;
    overflow: hidden;
  }

  .revenue-page__col {
    display: flex;
    flex-direction: column;
    align-self: stretch;
    height: 100%;
    min-height: 0;
    max-height: 100%;
  }

  .revenue-list-card,
  .revenue-panel-card {
    flex: 1 1 auto;
    height: 100%;
    min-height: 0;
    max-height: 100%;
  }
}

.revenue-page__col {
  min-width: 0;
  min-height: 0;
  width: 100%;
}

.revenue-list-card,
.revenue-panel-card {
  width: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.revenue-list-card__search {
  padding-block: 0 0.75rem;
}

.revenue-panel-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
}

.revenue-list {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  padding: 0 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-content: flex-start;
}

.revenue-list-item {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
  background: rgb(var(--v-theme-surface));
  text-align: left;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.revenue-list-item:hover {
  border-color: rgba(var(--v-theme-primary), 0.35);
  background: rgba(var(--v-theme-primary), 0.03);
}

.revenue-list-item--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.06);
}

.revenue-list-item__main {
  flex: 1 1 auto;
  min-width: 0;
}

.revenue-list-item__title {
  font-weight: 600;
  font-size: 0.9375rem;
  line-height: 1.3;
}

.revenue-list-item__rates {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 4px;
  font-size: 0.8125rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.85);
}

.revenue-list-item__rate-label {
  margin-right: 0.25rem;
  font-weight: 400;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.revenue-list-item__chevron {
  flex-shrink: 0;
  color: rgba(var(--v-theme-on-surface), 0.35);
}

.revenue-detail {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.revenue-detail__row {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.revenue-detail__label {
  font-size: 0.75rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.revenue-detail__value {
  font-size: 0.9375rem;
  font-weight: 600;
}

.revenue-detail__prev {
  margin-top: -0.5rem;
  font-size: 0.75rem;
  color: rgb(var(--v-theme-success));
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

.entity-stat-tile--warning {
  background: rgba(217, 119, 6, 0.1);
}

@media (max-width: 959px) {
  .revenue-page__stage {
    grid-template-columns: 1fr;
  }

  .revenue-list {
    max-height: 28rem;
  }
}
</style>
