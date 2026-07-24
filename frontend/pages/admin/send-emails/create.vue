<script setup>
const emails = useEmailsStore()
const form = reactive({
  owner_id: null,
  property_id: null,
  document_id: null,
  template_id: null,
  subject: '',
  body: '',
})

const owners = computed(() => (emails.data?.owners || []).map(o => ({
  title: o.full_name || o.owner_email || `#${o.id}`,
  value: o.id,
})))

const templates = computed(() => (emails.data?.templates || []).map(t => ({
  title: t.name || `#${t.id}`,
  value: t.id,
})))

const documents = computed(() => (emails.data?.documents || []).map(d => ({
  title: d.name || d.document || `#${d.id}`,
  value: d.id,
})))

onMounted(() => emails.load())

const submit = async () => {
  try {
    await emails.send({
      ...form,
      property_id: form.property_id || undefined,
      document_id: form.document_id || undefined,
      template_id: form.template_id || undefined,
    })
    Object.assign(form, {
      owner_id: null,
      property_id: null,
      document_id: null,
      template_id: null,
      subject: '',
      body: '',
    })
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard title="Send email">
    <VCardText>
      <VAlert
        v-if="emails.errors.general"
        type="error"
        class="mb-4"
      >
        {{ emails.errors.general[0] }}
      </VAlert>
      <VForm @submit.prevent="submit">
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <BaseSelect
              v-model="form.owner_id"
              label="Owner"
              :items="owners"
              :error="emails.errors.owner_id"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <BaseInput
              v-model="form.property_id"
              label="Property ID"
              type="number"
              :error="emails.errors.property_id"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <BaseSelect
              v-model="form.template_id"
              label="Template"
              :items="templates"
              clearable
              :error="emails.errors.template_id"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <BaseSelect
              v-model="form.document_id"
              label="Document"
              :items="documents"
              clearable
              :error="emails.errors.document_id"
            />
          </VCol>
          <VCol cols="12">
            <BaseInput
              v-model="form.subject"
              label="Subject"
              :error="emails.errors.subject"
            />
          </VCol>
          <VCol cols="12">
            <BaseTextarea
              v-model="form.body"
              label="Body"
              :error="emails.errors.body"
              rows="8"
            />
          </VCol>
        </VRow>
        <BaseButton
          type="submit"
          label="Send"
          :loading="emails.loading"
        />
      </VForm>
    </VCardText>
  </VCard>
</template>
