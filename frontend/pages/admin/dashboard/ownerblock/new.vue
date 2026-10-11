<script setup>
import { emptyOwnerBlockForm } from '@/constants/ownerBlocks'
import { MOCK_PROPERTIES } from '@/mocks/properties'

const ownerBlocks = useOwnerBlocksStore()
const properties = usePropertiesStore()

const form = reactive(emptyOwnerBlockForm('create'))

const propertyOptions = computed(() => {
  const data = properties.data?.data || properties.data || []
  const list = Array.isArray(data) && data.length ? data : MOCK_PROPERTIES

  return [
    { title: 'Select', value: null },
    ...list.map(property => ({
      title: `${property.property_title} - ${property.street_address || ''}`.trim(),
      value: property.id,
      property,
    })),
  ]
})

onMounted(async () => {
  await Promise.all([
    properties.load(),
    ownerBlocks.load(),
  ])
})

const onFormUpdate = value => {
  Object.assign(form, value)
}

const submit = async () => {
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

    await ownerBlocks.create(payload)
    await navigateTo('/admin/dashboard/ownerblock/all')
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
      title="Add new block"
      subtitle="Owner Calendar Blocks"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Back to list"
          to="/admin/dashboard/ownerblock/all"
        />
      </template>
    </PageHeader>

    <VAlert
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Looking for an existing owner block? Use the
      <BaseLink to="/admin/dashboard/ownercalendarview">
        calendar view
      </BaseLink>
      or the
      <BaseLink to="/admin/dashboard/ownerblock/all">
        blocks list
      </BaseLink>
      to edit and delete.
    </VAlert>

    <VCard>
      <VCardItem>
        <VCardTitle>Owner Calendar Block</VCardTitle>
      </VCardItem>
      <VCardText>
        <AppAlert :errors="ownerBlocks.errors" />
        <OwnerBlockForm
          :model-value="form"
          mode="create"
          submit-label="Save block"
          :property-options="propertyOptions"
          :loading="ownerBlocks.loading"
          :errors="ownerBlocks.errors"
          @update:model-value="onFormUpdate"
          @submit="submit"
          @cancel="navigateTo('/admin/dashboard/ownerblock/all')"
        />
      </VCardText>
    </VCard>
  </div>
</template>
