<script setup>
import { api } from '@/services/http'

definePageMeta({ middleware: 'auth' })

const entities = useAdminEntitiesStore()
const rows = computed(() => entities.records['/admin/cms'] || [])
const form = reactive({ page_id: 'owner-agreement', content: '' })
const editingPageId = ref(null)

const edit = row => {
  editingPageId.value = row.page_id
  Object.assign(form, {
    page_id: row.page_id ?? 'owner-agreement',
    content: row.content ?? '',
  })
}

const resetForm = () => {
  editingPageId.value = null
  Object.assign(form, { page_id: 'owner-agreement', content: '' })
}

const submit = async () => {
  try {
    await entities.run(() => api.put(`/admin/cms/${form.page_id}`, { content: form.content }))
    resetForm()
    await entities.list('/admin/cms')
  }
  catch {
    // Store exposes validation errors.
  }
}

onMounted(() => entities.list('/admin/cms'))
</script>

<template>
  <div>
    <PageHeader
      title="Manage Content"
      subtitle="Edit CMS page content (legacy owner-agreement and related pages)"
    />
    <VRow>
      <VCol
        cols="12"
        md="5"
      >
        <VCard :title="editingPageId ? `Edit ${editingPageId}` : 'Edit page content'">
          <VCardText>
            <AppAlert :errors="entities.errors" />
            <VForm @submit.prevent="submit">
              <BaseInput
                v-model="form.page_id"
                label="Page ID"
                :error="entities.errors.page_id"
              />
              <BaseTextarea
                v-model="form.content"
                label="Content"
                :error="entities.errors.content"
              />
              <div class="d-flex flex-wrap gap-2 mt-2">
                <BaseButton
                  type="submit"
                  label="Save content"
                  :loading="entities.loading"
                />
                <BaseButton
                  v-if="editingPageId"
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
        md="7"
      >
        <DataTableShell
          title="CMS pages"
          :loading="entities.loading"
          :errors="entities.errors"
          :empty="rows.length === 0"
          :empty-colspan="3"
          empty-title="No CMS pages found"
        >
          <template #head>
            <thead>
              <tr>
                <th>Page ID</th>
                <th>Content preview</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>
          <tr
            v-for="row in rows"
            :key="row.id || row.page_id"
          >
            <td>{{ row.page_id }}</td>
            <td>{{ String(row.content || '').slice(0, 120) }}</td>
            <td>
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                @click="edit(row)"
              />
            </td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>
  </div>
</template>
