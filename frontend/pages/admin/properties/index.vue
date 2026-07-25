<script setup>
const properties = usePropertiesStore()

const rows = computed(() => {
  const data = properties.data?.data || properties.data || []

  return Array.isArray(data) ? data : []
})

const form = reactive({
  property_title: '',
  region_id: '',
  subregion_id: '',
  hostaway_listing_id: '',
  street_address: '',
  city: '',
  state: '',
  zipcode: '',
  status: true,
})

const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

onMounted(() => properties.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    property_title: '',
    region_id: '',
    subregion_id: '',
    hostaway_listing_id: '',
    street_address: '',
    city: '',
    state: '',
    zipcode: '',
    status: true,
  })
}

const edit = property => {
  editingId.value = property.id
  Object.assign(form, {
    property_title: property.property_title || '',
    region_id: property.region_id || '',
    subregion_id: property.subregion_id || '',
    hostaway_listing_id: property.hostaway_listing_id || '',
    street_address: property.street_address || '',
    city: property.city || '',
    state: property.state || '',
    zipcode: property.zipcode || '',
    status: property.status ?? true,
  })
}

const submit = async () => {
  try {
    const payload = {
      ...form,
      region_id: form.region_id || null,
      subregion_id: form.subregion_id || null,
      hostaway_listing_id: form.hostaway_listing_id || null,
    }

    if (editingId.value)
      await properties.update(editingId.value, payload)
    else
      await properties.create(payload)

    resetForm()
    await properties.load()
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
    await properties.remove(confirmDelete.id)
    confirmDelete.open = false
    await properties.load()
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
      title="Properties"
      subtitle="Manage listings and owner links"
    />

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard :title="editingId ? 'Edit property' : 'Add property'">
          <VCardText>
            <AppAlert :errors="properties.errors" />
            <VForm @submit.prevent="submit">
              <BaseInput
                v-model="form.property_title"
                label="Title"
                :error="properties.errors.property_title"
              />
              <BaseInput
                v-model="form.region_id"
                label="Region ID"
                type="number"
                :error="properties.errors.region_id"
              />
              <BaseInput
                v-model="form.subregion_id"
                label="Subregion ID"
                type="number"
                :error="properties.errors.subregion_id"
              />
              <BaseInput
                v-model="form.hostaway_listing_id"
                label="Hostaway listing ID"
                type="number"
                :error="properties.errors.hostaway_listing_id"
              />
              <BaseInput
                v-model="form.street_address"
                label="Street address"
                :error="properties.errors.street_address"
              />
              <BaseInput
                v-model="form.city"
                label="City"
                :error="properties.errors.city"
              />
              <BaseInput
                v-model="form.state"
                label="State"
                :error="properties.errors.state"
              />
              <BaseInput
                v-model="form.zipcode"
                label="Zipcode"
                :error="properties.errors.zipcode"
              />
              <BaseCheckbox
                v-model="form.status"
                label="Active / live"
              />
              <div class="d-flex flex-wrap gap-2 mt-2">
                <BaseButton
                  type="submit"
                  :label="editingId ? 'Update property' : 'Create property'"
                  :loading="properties.loading"
                />
                <BaseButton
                  v-if="editingId"
                  type="button"
                  variant="tonal"
                  label="Cancel"
                  @click="resetForm"
                />
              </div>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All properties"
          :loading="properties.loading"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No properties found"
        >
          <template #head>
            <thead>
              <tr>
                <th>Title</th>
                <th>City</th>
                <th>Status</th>
                <th>Region</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="property in rows"
            :key="property.id"
          >
            <td>{{ property.property_title }}</td>
            <td>{{ property.city || '—' }}</td>
            <td>{{ property.status ? 'Live' : 'Inactive' }}</td>
            <td>{{ property.region?.name || property.region_id || '—' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(property)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(property.id)"
              />
            </td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete property?"
      message="This soft-deletes the property (deleted flag)."
      confirm-label="Delete"
      confirm-color="error"
      :loading="properties.loading"
      @confirm="onDelete"
    />
  </div>
</template>
