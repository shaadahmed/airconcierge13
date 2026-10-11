<script setup>
import { ownerBlockFormFromRecord } from '@/constants/ownerBlocks'
import { ownerBlockPropertyTitle } from '@/mocks/ownerBlocks'
import { MOCK_PROPERTIES } from '@/mocks/properties'

const route = useRoute()
const ownerBlocks = useOwnerBlocksStore()
const properties = usePropertiesStore()

const form = reactive(ownerBlockFormFromRecord(null))
const loaded = ref(false)

const propertyOptions = computed(() => {
  const data = properties.data?.data || properties.data || []
  const list = Array.isArray(data) && data.length ? data : MOCK_PROPERTIES
  const options = [
    { title: 'Select', value: null },
    ...list.map(property => ({
      title: `${property.property_title} - ${property.street_address || ''}`.trim(),
      value: property.id,
    })),
  ]

  const currentId = form.property_id ?? ownerBlocks.current?.property_id ?? ownerBlocks.current?.property?.id
  if (currentId != null && !options.some(option => String(option.value) === String(currentId))) {
    options.splice(1, 0, {
      title: ownerBlockPropertyTitle(ownerBlocks.current) || `Property #${currentId}`,
      value: currentId,
    })
  }

  return options
})

onMounted(async () => {
  await Promise.all([
    properties.load(),
    ownerBlocks.load(),
    ownerBlocks.loadOne(route.params.id),
  ])

  Object.assign(form, ownerBlockFormFromRecord(ownerBlocks.current))
  loaded.value = true
})

const onFormUpdate = value => {
  Object.assign(form, value)
}

const save = async () => {
  try {
    await ownerBlocks.save(route.params.id, { ...form })
    await navigateTo('/admin/dashboard/ownerblock/all')
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Update block"
      :subtitle="ownerBlockPropertyTitle(ownerBlocks.current) || 'Owner Calendar Blocks'"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Back to list"
          to="/admin/dashboard/ownerblock/all"
        />
      </template>
    </PageHeader>

    <VCard>
      <VCardItem>
        <VCardTitle>Update block</VCardTitle>
      </VCardItem>
      <VCardText>
        <AppAlert :errors="ownerBlocks.errors" />
        <OwnerBlockForm
          v-if="loaded"
          :model-value="form"
          mode="edit"
          submit-label="Update"
          :property-options="propertyOptions"
          :loading="ownerBlocks.loading"
          :errors="ownerBlocks.errors"
          @update:model-value="onFormUpdate"
          @submit="save"
          @cancel="navigateTo('/admin/dashboard/ownerblock/all')"
        />
        <div
          v-else
          class="text-body-2 text-medium-emphasis"
        >
          Loading block…
        </div>
      </VCardText>
    </VCard>
  </div>
</template>
