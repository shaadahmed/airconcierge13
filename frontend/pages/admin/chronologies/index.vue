<script setup>
const chronologies = useChronologiesStore()
const rows = computed(() => chronologies.data?.data || chronologies.data || [])

onMounted(() => chronologies.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>Chronologies</VCardTitle>
      <template #append>
        <BaseLink
          to="/admin/chronologies/create"
          class="text-primary"
        >
          Create chronology
        </BaseLink>
      </template>
    </VCardItem>
    <VAlert
      v-if="chronologies.errors.general"
      type="error"
      class="ma-4"
    >
      {{ chronologies.errors.general[0] }}
    </VAlert>
    <VTable>
      <thead><tr><th>Name</th><th>Start date</th><th /></tr></thead>
      <tbody>
        <tr
          v-for="chronology in rows"
          :key="chronology.id"
        >
          <td>{{ chronology.name }}</td>
          <td>{{ chronology.startdate }}</td>
          <td>
            <BaseLink
              :to="`/admin/chronologies/${chronology.id}`"
              class="text-primary"
            >
              View
            </BaseLink>
          </td>
        </tr>
      </tbody>
    </VTable>
  </VCard>
</template>
