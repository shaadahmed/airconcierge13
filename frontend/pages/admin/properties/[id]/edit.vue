<script setup>
import { createEmptyPropertyForm, mapPropertyToForm, serializePropertyForm } from '@/utils/propertyForm'

const route = useRoute()
const properties = usePropertiesStore()
const form = reactive(createEmptyPropertyForm())
const formSource = computed(() => form)
const loaded = ref(false)

const {
  usingMocks,
  loadLookups,
  ownerOptions,
  regionOptions,
  subregionOptions,
  cleanerOptions,
  parentPropertyOptions,
} = usePropertyFormOptions(formSource)

const submit = async () => {
  try {
    await properties.update(route.params.id, serializePropertyForm(form))
    await navigateTo(`/admin/properties/${route.params.id}`)
  }
  catch {
    // Store exposes validation errors.
  }
}

const cancel = async () => {
  await navigateTo(`/admin/properties/${route.params.id}`)
}

const onFormUpdate = value => {
  Object.assign(form, value)
}

onMounted(async () => {
  await loadLookups()

  try {
    const property = await properties.loadOne(route.params.id)
    Object.assign(form, mapPropertyToForm(property))
    loaded.value = true
  }
  catch {
    loaded.value = false
  }
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Edit property"
      :subtitle="form.property_title || 'Update listing fields'"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="View property"
          :to="`/admin/properties/${route.params.id}`"
        />
        <BaseButton
          variant="tonal"
          label="Back to list"
          to="/admin/properties"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="usingMocks || properties.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Form options and save use UI preview data until the properties API is connected.
    </VAlert>

    <AppAlert :errors="properties.errors" />

    <VProgressLinear
      v-if="properties.loading && !loaded"
      indeterminate
      class="mb-4"
    />

    <template v-if="loaded">
      <VCard class="mb-6">
        <VCardItem>
          <VCardTitle>Property details</VCardTitle>
        </VCardItem>
        <VCardText>
          <PropertyForm
            :model-value="form"
            mode="edit"
            :errors="properties.errors"
            :loading="properties.loading"
            submit-label="Update property"
            :owner-options="ownerOptions"
            :region-options="regionOptions"
            :subregion-options="subregionOptions"
            :parent-property-options="parentPropertyOptions"
            :cleaner-options="cleanerOptions"
            @update:model-value="onFormUpdate"
            @submit="submit"
            @cancel="cancel"
          />
        </VCardText>
      </VCard>

      <div class="text-h6 mb-3">
        Related sections
      </div>
      <PropertyRelatedPanels :property="properties.current" />
    </template>

    <EmptyState
      v-else-if="!properties.loading"
      title="Property not found"
      description="This listing may have been removed or the id is invalid."
    />
  </div>
</template>
