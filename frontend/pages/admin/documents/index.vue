<script setup>
const documents = useDocumentsStore()

const rows = computed(() => {
  const data = documents.data?.data || documents.data || []

  return Array.isArray(data) ? data : []
})

const form = reactive({
  name: '',
  ownerspecific: '',
  type: '',
  signid: '',
  zohoactionid: '',
  roletitle: '',
})

const file = ref(null)
const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })

onMounted(() => documents.load())

const resetForm = () => {
  editingId.value = null
  file.value = null
  Object.assign(form, {
    name: '',
    ownerspecific: '',
    type: '',
    signid: '',
    zohoactionid: '',
    roletitle: '',
  })
}

const edit = document => {
  editingId.value = document.id
  file.value = null
  Object.assign(form, {
    name: document.name || '',
    ownerspecific: document.ownerspecific || '',
    type: document.type ?? '',
    signid: document.signid || '',
    zohoactionid: document.zohoactionid || '',
    roletitle: document.roletitle || '',
  })
}

const onFileChange = event => {
  file.value = event.target.files?.[0] || null
}

const submit = async () => {
  try {
    if (editingId.value) {
      await documents.update(editingId.value, {
        name: form.name,
        ownerspecific: form.ownerspecific || null,
        type: form.type === '' ? null : Number(form.type),
        signid: form.signid || null,
        zohoactionid: form.zohoactionid || null,
        roletitle: form.roletitle || null,
      })
    }
    else {
      const payload = new FormData()

      payload.append('name', form.name)
      if (form.ownerspecific)
        payload.append('ownerspecific', form.ownerspecific)
      if (form.type !== '')
        payload.append('type', String(form.type))
      if (form.signid)
        payload.append('signid', form.signid)
      if (form.zohoactionid)
        payload.append('zohoactionid', form.zohoactionid)
      if (form.roletitle)
        payload.append('roletitle', form.roletitle)
      if (file.value)
        payload.append('file', file.value)

      await documents.create(payload)
    }

    resetForm()
    await documents.load()
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
    await documents.remove(confirmDelete.id)
    confirmDelete.open = false
    await documents.load()
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
      title="Documents"
      subtitle="Uploads and Zoho Sign document records"
    />

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard :title="editingId ? 'Edit document' : 'Add document'">
          <VCardText>
            <AppAlert :errors="documents.errors" />
            <VForm @submit.prevent="submit">
              <BaseInput
                v-model="form.name"
                label="Name"
                :error="documents.errors.name"
              />
              <BaseInput
                v-model="form.ownerspecific"
                label="Owner specific"
                :error="documents.errors.ownerspecific"
              />
              <BaseInput
                v-model="form.type"
                label="Type"
                type="number"
                :error="documents.errors.type"
              />
              <BaseInput
                v-model="form.signid"
                label="Sign ID"
                :error="documents.errors.signid"
              />
              <BaseInput
                v-model="form.zohoactionid"
                label="Zoho action ID"
                :error="documents.errors.zohoactionid"
              />
              <BaseInput
                v-model="form.roletitle"
                label="Role title"
                :error="documents.errors.roletitle"
              />
              <div
                v-if="!editingId"
                class="mb-4"
              >
                <label class="text-body-2 d-block mb-1">File</label>
                <input
                  type="file"
                  @change="onFileChange"
                >
              </div>
              <div class="d-flex flex-wrap gap-2">
                <BaseButton
                  type="submit"
                  :label="editingId ? 'Update document' : 'Create document'"
                  :loading="documents.loading"
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
          title="All documents"
          :loading="documents.loading"
          :empty="rows.length === 0"
          :empty-colspan="4"
          empty-title="No documents found"
        >
          <template #head>
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="document in rows"
            :key="document.id"
          >
            <td>{{ document.name }}</td>
            <td>{{ document.type ?? '—' }}</td>
            <td>{{ document.roletitle || '—' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(document)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="askDelete(document.id)"
              />
            </td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete document?"
      message="This permanently removes the document record."
      confirm-label="Delete"
      confirm-color="error"
      :loading="documents.loading"
      @confirm="onDelete"
    />
  </div>
</template>
