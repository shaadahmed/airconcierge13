<script setup>
const chronologies = useChronologiesStore()

const rows = computed(() => {
  const data = chronologies.data?.data || chronologies.data || []

  return Array.isArray(data) ? data : []
})

onMounted(() => chronologies.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Chronologies"
      subtitle="Email chronology campaigns"
    >
      <template #actions>
        <BaseButton
          label="Create chronology"
          color="primary"
          to="/admin/chronologies/create"
        />
      </template>
    </PageHeader>

    <DataTableShell
      title="All chronologies"
      :loading="chronologies.loading"
      :errors="chronologies.errors"
      :empty="rows.length === 0"
      :empty-colspan="3"
      empty-title="No chronologies found"
    >
      <template #head>
        <thead>
          <tr>
            <th>Name</th>
            <th>Start date</th>
            <th />
          </tr>
        </thead>
      </template>

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
    </DataTableShell>
  </div>
</template>
