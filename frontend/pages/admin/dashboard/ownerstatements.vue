<script setup>
import { api } from '@/services/http'

const loading = ref(false)
const errors = ref({})
const payload = ref({ route: null, owners: [] })

/** @type {import('vue').Ref<'dashboard' | 'filter'>} */
const panelMode = ref('dashboard')

const filters = reactive({
  name: '',
})

const allRows = computed(() => Array.isArray(payload.value.owners) ? payload.value.owners : [])

const rows = computed(() => {
  const nameQuery = filters.name.trim().toLowerCase()

  return allRows.value.filter(owner => {
    if (!nameQuery)
      return true

    return String(owner.full_name || '').toLowerCase().includes(nameQuery)
  })
})

const stats = computed(() => ({
  total: allRows.value.length,
  withNames: allRows.value.filter(owner => Boolean(owner.full_name)).length,
}))

const panelTitle = computed(() => panelMode.value === 'filter' ? 'Filter owners' : 'Statements overview')

const openPanel = mode => {
  panelMode.value = panelMode.value === mode ? 'dashboard' : mode
}

const clearFilters = () => {
  filters.name = ''
}

const load = async () => {
  loading.value = true
  errors.value = {}

  try {
    const response = await api.get('/admin/dashboard/ownerstatements')
    payload.value = response.data || { route: null, owners: [] }
  }
  catch (error) {
    errors.value = error?.data?.errors || { general: [error?.data?.message || 'Unable to load owner statements.'] }
  }
  finally {
    loading.value = false
  }
}

onMounted(load)

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="P&L Owner Statements"
      subtitle="Owner statement dashboard"
    >
      <template #actions>
        <BaseButton
          label="Open full P&L"
          color="primary"
          to="/admin/owner-statements"
        />
      </template>
    </PageHeader>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="Owners"
          :loading="loading"
          :errors="errors"
          :empty="rows.length === 0"
          :empty-colspan="2"
          empty-title="No owners found"
          :empty-description="allRows.length && rows.length === 0 ? 'No owners match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Owner</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="owner in rows"
            :key="owner.id"
          >
            <td>{{ owner.full_name || `Owner #${owner.id}` }}</td>
            <td>
              <BaseButton
                size="small"
                variant="tonal"
                label="Open P&L"
                :to="`/admin/owner-statements`"
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
              <BaseButton
                size="small"
                :variant="panelMode === 'filter' ? 'flat' : 'tonal'"
                :color="panelMode === 'filter' ? 'primary' : undefined"
                label="Filter"
                prepend-icon="bx-filter-alt"
                @click="openPanel('filter')"
              />
            </template>
          </VCardItem>

          <VCardText>
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of owners available for statements
              </p>

              <VRow dense>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Owners
                    </div>
                    <div class="text-h5">
                      {{ stats.total }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Named
                    </div>
                    <div class="text-h5">
                      {{ stats.withNames }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <VAlert
                type="info"
                variant="tonal"
                density="compact"
                class="mt-4"
              >
                Use Open full P&L for property date-range statements.
              </VAlert>
            </div>

            <div v-else>
              <BaseInput
                v-model="filters.name"
                label="Owner name contains"
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

.entity-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}
</style>
