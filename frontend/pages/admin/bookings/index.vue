<script setup>
const bookings = useBookingsStore()

const rows = computed(() => {
  const data = bookings.data?.data || bookings.data || []

  return Array.isArray(data) ? data : []
})

onMounted(() => bookings.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Bookings"
      subtitle="Reservations across properties"
    />

    <DataTableShell
      title="All bookings"
      :loading="bookings.loading"
      :errors="bookings.errors"
      :empty="rows.length === 0"
      :empty-colspan="4"
      empty-title="No bookings found"
    >
      <template #head>
        <thead>
          <tr>
            <th>Booking</th>
            <th>Property</th>
            <th>Dates</th>
            <th />
          </tr>
        </thead>
      </template>

      <tr
        v-for="booking in rows"
        :key="booking.id"
      >
        <td>{{ booking.booking_code || booking.id }}</td>
        <td>{{ booking.property?.name || booking.property_id || '—' }}</td>
        <td>{{ booking.reservation_start_date || '—' }} – {{ booking.reservation_end_date || '—' }}</td>
        <td>
          <BaseLink
            :to="`/admin/bookings/${booking.id}`"
            class="text-primary"
          >
            View
          </BaseLink>
        </td>
      </tr>
    </DataTableShell>
  </div>
</template>
