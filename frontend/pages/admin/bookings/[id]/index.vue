<script setup>
import {
  bookingPropertyTitle,
} from '@/mocks/bookings'

const route = useRoute()
const bookings = useBookingsStore()

const booking = computed(() => bookings.current)

const load = async () => {
  try {
    await bookings.loadOne(route.params.id)
  }
  catch {
    // Store exposes errors.
  }
}

const goBack = async () => {
  await navigateTo('/admin/bookings')
}

onMounted(load)

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      :title="booking?.booking_code || bookingPropertyTitle(booking) || 'Booking'"
      subtitle="Booking details"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Back to list"
          @click="goBack"
        />
        <BaseButton
          v-if="booking"
          color="primary"
          label="Edit"
          prepend-icon="bx-edit"
          @click="navigateTo(`/admin/bookings/${booking.id}/edit`)"
        />
      </template>
    </PageHeader>

    <VAlert
      v-if="bookings.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Showing UI preview data until the bookings API is connected.
    </VAlert>

    <AppAlert :errors="bookings.errors" />

    <VProgressLinear
      v-if="bookings.loading && !booking"
      indeterminate
      class="mb-4"
    />

    <BookingView
      v-if="booking"
      :booking="booking"
    />

    <EmptyState
      v-else-if="!bookings.loading"
      title="Booking not found"
      description="This booking may have been removed or the id is invalid."
    />
  </div>
</template>
