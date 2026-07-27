<script setup>
import { api } from '@/services/http'

const entities = useAdminEntitiesStore()
const form = reactive({ name: '', email: '' })

onMounted(async () => {
  try {
    const response = await entities.run(() => api.get('/admin/dashboard/profile'))
    Object.assign(form, response.data ?? response)
  }
  catch {
    // The store exposes errors.
  }
})

const submit = async () => {
  try {
    await entities.run(() => api.put('/admin/dashboard/profile', { ...form }))
  }
  catch {
    // The store exposes validation errors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader title="Profile" subtitle="Update your administrator profile" />
    <VCard title="Profile details">
      <VCardText>
        <AppAlert :errors="entities.errors" />
        <VForm @submit.prevent="submit">
          <BaseInput v-model="form.name" label="Name" :error="entities.errors.name" />
          <BaseInput v-model="form.email" label="Email" type="email" :error="entities.errors.email" />
          <BaseButton type="submit" label="Save profile" :loading="entities.loading" />
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
