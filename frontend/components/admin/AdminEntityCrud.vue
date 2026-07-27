<script setup>
const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  endpoint: { type: String, required: true },
  fields: { type: Array, required: true },
  columns: { type: Array, default: null },
})

const entities = useAdminEntitiesStore()
const editingId = ref(null)
const confirmDelete = reactive({ open: false, id: null })
const tableColumns = computed(() => props.columns || props.fields)

const emptyForm = () => Object.fromEntries(props.fields.map(field => {
  if (field.type === 'checkbox')
    return [field.key, field.default ?? false]

  return [field.key, field.default ?? '']
}))

const form = reactive(emptyForm())
const rows = computed(() => entities.records[props.endpoint] || [])

const resetForm = () => {
  editingId.value = null
  Object.assign(form, emptyForm())
}

const edit = row => {
  editingId.value = row.id
  Object.assign(form, Object.fromEntries(props.fields.map(field => {
    const value = row[field.key]

    if (field.type === 'checkbox')
      return [field.key, Boolean(value)]

    return [field.key, value ?? field.default ?? '']
  })))
}

const payload = () => {
  const data = {}

  props.fields.forEach(field => {
    let value = form[field.key]

    if (field.type === 'number' && value !== '' && value !== null && value !== undefined)
      value = Number(value)

    if (field.type === 'checkbox')
      value = Boolean(value)

    data[field.key] = value
  })

  return data
}

const submit = async () => {
  try {
    if (editingId.value)
      await entities.update(props.endpoint, editingId.value, payload())
    else
      await entities.create(props.endpoint, payload())

    resetForm()
    await entities.list(props.endpoint)
  }
  catch {
    // Store exposes validation errors.
  }
}

const remove = async () => {
  try {
    await entities.remove(props.endpoint, confirmDelete.id)
    confirmDelete.open = false
    await entities.list(props.endpoint)
  }
  catch {
    // Store exposes errors.
  }
}

const displayValue = (row, field) => {
  const value = row[field.key]

  if (field.type === 'checkbox')
    return value ? 'Yes' : 'No'

  if (value === null || value === undefined || value === '')
    return '—'

  return value
}

onMounted(async () => {
  try {
    await entities.list(props.endpoint)
  }
  catch {
    // Store exposes loading errors.
  }
})
</script>

<template>
  <div>
    <PageHeader
      :title="title"
      :subtitle="subtitle"
    />
    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard :title="editingId ? `Edit ${title}` : `Add ${title}`">
          <VCardText>
            <AppAlert :errors="entities.errors" />
            <VForm @submit.prevent="submit">
              <template
                v-for="field in fields"
                :key="field.key"
              >
                <BaseCheckbox
                  v-if="field.type === 'checkbox'"
                  v-model="form[field.key]"
                  :label="field.label"
                  :error="entities.errors[field.key]"
                  class="mb-3"
                />
                <BaseTextarea
                  v-else-if="field.type === 'textarea'"
                  v-model="form[field.key]"
                  :label="field.label"
                  :error="entities.errors[field.key]"
                />
                <BaseInput
                  v-else
                  v-model="form[field.key]"
                  :label="field.label"
                  :type="field.type || 'text'"
                  :error="entities.errors[field.key]"
                />
              </template>
              <div class="d-flex flex-wrap gap-2 mt-2">
                <BaseButton
                  type="submit"
                  :label="editingId ? 'Update' : 'Create'"
                  :loading="entities.loading"
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
          :title="`All ${title}`"
          :loading="entities.loading"
          :errors="entities.errors"
          :empty="rows.length === 0"
          :empty-colspan="tableColumns.length + 1"
          :empty-title="`No ${title.toLowerCase()} found`"
        >
          <template #head>
            <thead>
              <tr>
                <th
                  v-for="field in tableColumns"
                  :key="field.key"
                >
                  {{ field.label }}
                </th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>
          <tr
            v-for="row in rows"
            :key="row.id"
          >
            <td
              v-for="field in tableColumns"
              :key="field.key"
            >
              {{ displayValue(row, field) }}
            </td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(row)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="Object.assign(confirmDelete, { open: true, id: row.id })"
              />
            </td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete record?"
      message="This action cannot be undone."
      confirm-label="Delete"
      confirm-color="error"
      :loading="entities.loading"
      @confirm="remove"
    />
  </div>
</template>
