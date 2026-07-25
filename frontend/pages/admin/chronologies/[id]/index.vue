<script setup>
const route = useRoute()
const chronologies = useChronologiesStore()
const chronology = computed(() => chronologies.current || {})

onMounted(() => chronologies.loadOne(route.params.id))

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>{{ chronology.name || 'Chronology' }}</VCardTitle>
      <template #append>
        <BaseLink
          :to="`/admin/chronologies/${route.params.id}/edit`"
          class="text-primary"
        >
          Edit
        </BaseLink>
      </template>
    </VCardItem>
    <VCardText>
      <VAlert
        v-if="chronologies.errors.general"
        type="error"
        class="mb-4"
      >
        {{ chronologies.errors.general[0] }}
      </VAlert>
      <VList>
        <VListItem
          title="Start date"
          :subtitle="chronology.startdate || '—'"
        />
        <VListItem
          title="Option"
          :subtitle="String(chronology.chronologyoption ?? '—')"
        />
      </VList>
      <h3 class="text-h6 mt-6 mb-2">
        Orders
      </h3>
      <VTable>
        <thead><tr><th>Order</th><th>Template</th><th>Document</th></tr></thead>
        <tbody>
          <tr
            v-for="order in chronology.orders || []"
            :key="order.id"
          >
            <td>{{ order.order_no || order.id }}</td>
            <td>{{ order.template_id || '—' }}</td>
            <td>{{ order.document_id || '—' }}</td>
          </tr>
        </tbody>
      </VTable>
    </VCardText>
  </VCard>
</template>
