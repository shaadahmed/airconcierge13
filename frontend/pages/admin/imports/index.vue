<script setup>
const imports = useImportsStore()

const emails = computed(() => {
  const data = imports.emails?.data || imports.emails || []

  return Array.isArray(data) ? data : []
})

const ownersJson = ref('[\n  {\n    "owner_email": "",\n    "full_name": "",\n    "owner_phone": "",\n    "region_id": null\n  }\n]')
const propertiesJson = ref('[\n  {\n    "property_title": "",\n    "region_id": null,\n    "street_address": "",\n    "city": "",\n    "state": "",\n    "zipcode": ""\n  }\n]')

const emailForm = reactive({
  source: '',
  subject: '',
  body: '',
  from_email: '',
})

const parseError = ref('')
const successMessage = ref('')

onMounted(() => imports.loadEmails())

const parseRows = text => {
  parseError.value = ''
  try {
    const rows = JSON.parse(text)
    if (!Array.isArray(rows))
      throw new Error('JSON must be an array of rows.')

    return rows
  }
  catch (error) {
    parseError.value = error.message || 'Invalid JSON.'
    throw error
  }
}

const submitOwners = async () => {
  successMessage.value = ''
  try {
    const result = await imports.importOwners(parseRows(ownersJson.value))

    successMessage.value = `Imported ${result.imported ?? 0} owner row(s).`
  }
  catch {
    // Store / parse errors.
  }
}

const submitProperties = async () => {
  successMessage.value = ''
  try {
    const result = await imports.importProperties(parseRows(propertiesJson.value))

    successMessage.value = `Imported ${result.imported ?? 0} property row(s).`
  }
  catch {
    // Store / parse errors.
  }
}

const submitEmail = async () => {
  successMessage.value = ''
  try {
    await imports.storeEmail({ ...emailForm })
    Object.assign(emailForm, { source: '', subject: '', body: '', from_email: '' })
    successMessage.value = 'Imported email stored.'
  }
  catch {
    // Store errors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Imports"
      subtitle="Bulk owner/property rows and imported emails"
    />

    <VAlert
      v-if="successMessage"
      type="success"
      class="mb-4"
    >
      {{ successMessage }}
    </VAlert>
    <VAlert
      v-if="parseError"
      type="error"
      class="mb-4"
    >
      {{ parseError }}
    </VAlert>
    <AppAlert :errors="imports.errors" />

    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <VCard
          title="Import owners"
          class="mb-6"
        >
          <VCardText>
            <BaseTextarea
              v-model="ownersJson"
              label="Owner rows (JSON array)"
              rows="10"
            />
            <BaseButton
              label="Import owners"
              :loading="imports.loading"
              @click="submitOwners"
            />
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="6"
      >
        <VCard
          title="Import properties"
          class="mb-6"
        >
          <VCardText>
            <BaseTextarea
              v-model="propertiesJson"
              label="Property rows (JSON array)"
              rows="10"
            />
            <BaseButton
              label="Import properties"
              :loading="imports.loading"
              @click="submitProperties"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard title="Store imported email">
          <VCardText>
            <VForm @submit.prevent="submitEmail">
              <BaseInput
                v-model="emailForm.source"
                label="Source"
                :error="imports.errors.source"
              />
              <BaseInput
                v-model="emailForm.from_email"
                label="From email"
                :error="imports.errors.from_email"
              />
              <BaseInput
                v-model="emailForm.subject"
                label="Subject"
                :error="imports.errors.subject"
              />
              <BaseTextarea
                v-model="emailForm.body"
                label="Body"
                :error="imports.errors.body"
              />
              <BaseButton
                type="submit"
                label="Store email"
                :loading="imports.loading"
              />
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="Imported emails"
          :loading="imports.loading"
          :empty="emails.length === 0"
          :empty-colspan="4"
          empty-title="No imported emails"
        >
          <template #head>
            <thead>
              <tr>
                <th>ID</th>
                <th>From</th>
                <th>Subject</th>
                <th>Source</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="email in emails"
            :key="email.id"
          >
            <td>{{ email.id }}</td>
            <td>{{ email.from_email || '—' }}</td>
            <td>{{ email.subject || '—' }}</td>
            <td>{{ email.source || '—' }}</td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>
  </div>
</template>
