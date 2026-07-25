<script setup>
const auth = useAuthStore()

onMounted(() => {
  if (!auth.user)
    auth.fetchUser()
})

definePageMeta({ layout: 'blank' })
</script>

<template>
  <VContainer class="d-flex align-center justify-center min-vh-100">
    <VCard
      class="pa-8 text-center"
      max-width="560"
    >
      <VCardTitle class="text-h3">
        Air Concierge
      </VCardTitle>
      <VCardText>Property operations, bookings, payments, and owner communications.</VCardText>
      <BaseLink
        :to="auth.user ? '/admin/dashboard' : '/login'"
        class="text-primary font-weight-medium"
      >
        {{ auth.user ? 'Open dashboard' : 'Sign in' }}
      </BaseLink>
    </VCard>
  </VContainer>
</template>
