<script setup>
const zoho = useZohoStore()

onMounted(() => zoho.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Zoho Sign"
      subtitle="Document signature integration status"
    />

    <VCard title="Connection status">
      <VCardText>
        <AppAlert :errors="zoho.errors" />

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
  </div>
</template>
