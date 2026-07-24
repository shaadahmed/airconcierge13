<script setup>
const bookings = useBookingsStore()
const rows = computed(() => bookings.data?.data || bookings.data || [])

onMounted(() => bookings.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard title="Bookings">
    <VAlert
      v-if="bookings.errors.general"
      type="error"
      class="ma-4"
    >
      {{ bookings.errors.general[0] }}
    </VAlert>
    <VTable>
      <thead>
        <tr>
          <th>Booking</th>
          <th>Property</th>
          <th>Dates</th>
          <th />
        </tr>
      </thead>
      <tbody>
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
      </tbody>
    </VTable>
    <VProgressLinear
      v-if="bookings.loading"
      indeterminate
    />
  </VCard>
</template>
