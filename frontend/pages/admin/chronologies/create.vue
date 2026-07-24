<script setup>
const chronologies = useChronologiesStore()
const form = reactive({ name: '', startdate: '', chronologyoption: 0 })

const submit = async () => {
  try {
    const response = await chronologies.create(form)

    await navigateTo(`/admin/chronologies/${response.data.id}`)
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard title="Create chronology">
    <VCardText>
      <ChronologyForm
        :model-value="form"
        :errors="chronologies.errors"
        :loading="chronologies.loading"
        @submit="submit"
      />
    </VCardText>
  </VCard>
</template>
