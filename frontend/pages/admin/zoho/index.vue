<script setup>
const zoho = useZohoStore()

onMounted(() => zoho.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard title="Zoho Sign">
    <VCardText>
      <VAlert
        v-if="zoho.errors.general"
        type="error"
        class="mb-4"
      >
        {{ zoho.errors.general[0] }}
      </VAlert>

      <VProgressLinear
        v-if="zoho.loading"
        indeterminate
        class="mb-4"
      />

      <VList lines="two">
        <VListItem
          title="Configured"
          :subtitle="zoho.data?.configured ? 'Yes' : 'No'"
        />
        <VListItem
          title="Expires soon"
          :subtitle="zoho.data?.expires_soon ? 'Yes' : 'No'"
        />
        <VListItem
          title="Updated at"
          :subtitle="zoho.data?.updated_at || '—'"
        />
      </VList>
    </VCardText>
  </VCard>
</template>
