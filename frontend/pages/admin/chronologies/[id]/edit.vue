<script setup>
const route = useRoute()
const chronologies = useChronologiesStore()
const form = reactive({ name: '', startdate: '', chronologyoption: 0 })

onMounted(async () => {
  await chronologies.loadOne(route.params.id)
  Object.assign(form, {
    name: chronologies.current?.name || '',
    startdate: chronologies.current?.startdate || '',
    chronologyoption: chronologies.current?.chronologyoption ?? 0,
  })
})

const submit = async () => {
  try {
    await chronologies.update(route.params.id, form)
    await navigateTo(`/admin/chronologies/${route.params.id}`)
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard title="Edit chronology">
    <VCardText>
      <ChronologyForm
        :model-value="form"
        :errors="chronologies.errors"
        :loading="chronologies.loading"
        submit-label="Update chronology"
        @submit="submit"
      />
    </VCardText>
  </VCard>
</template>
