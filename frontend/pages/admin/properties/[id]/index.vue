<script setup>
import {
  propertyStatusColor,
  propertyStatusLabel,
} from '@/constants/properties'

const route = useRoute()
const properties = usePropertiesStore()
const confirmDelete = reactive({ open: false })

const property = computed(() => properties.current)

const load = async () => {
  try {
    await properties.loadOne(route.params.id)
  }
  catch {
    // Store exposes errors.
  }
}

const onDelete = async () => {
  try {
    await properties.remove(route.params.id)
    confirmDelete.open = false
    await navigateTo('/admin/properties')
  }
  catch {
    // Store exposes errors.
  }
}

onMounted(load)

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      :title="property?.property_title || 'Property'"
      subtitle="Property details"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Back to list"
          to="/admin/properties"
        />
        <BaseButton
          v-if="property"
          color="primary"
          label="Edit"
          prepend-icon="bx-edit"
          :to="`/admin/properties/${property.id}/edit`"
        />
        <BaseButton
          v-if="property"
          variant="tonal"
          color="error"
          label="Delete"
          @click="confirmDelete.open = true"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="properties.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Showing UI preview data until the properties API is connected.
    </VAlert>

    <AppAlert :errors="properties.errors" />

    <VProgressLinear
      v-if="properties.loading && !property"
      indeterminate
      class="mb-4"
    />

    <template v-if="property">
      <div class="d-flex align-center flex-wrap gap-2 mb-4">
        <VChip
          size="small"
          :color="propertyStatusColor(property.status)"
          label
        >
          {{ propertyStatusLabel(property.status) }}
        </VChip>
        <span class="text-body-2 text-medium-emphasis">
          ID #{{ property.id }}
        </span>
      </div>

      <PropertyView :property="property" />

      <div class="mt-6">
        <div class="text-h6 mb-3">
          Related information
        </div>
        <PropertyRelatedPanels :property="property" />
      </div>
    </template>

    <EmptyState
      v-else-if="!properties.loading"
      title="Property not found"
      description="This listing may have been removed or the id is invalid."
    />

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete property?"
      message="This soft-deletes the property."
      confirm-label="Delete"
      confirm-color="error"
      :loading="properties.loading"
      @confirm="onDelete"
    />
  </div>
</template>
