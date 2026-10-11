<script setup>
import { bookingFormFromRecord, moneyNumber } from '@/constants/bookings'
import { bookingPropertyTitle } from '@/mocks/bookings'

const route = useRoute()
const bookings = useBookingsStore()
const properties = usePropertiesStore()

const form = reactive(bookingFormFromRecord(null))
const loaded = ref(false)

const propertyOptions = computed(() => {
  const data = properties.data?.data || properties.data || []
  const list = Array.isArray(data) ? data : []
  const options = list.map(property => ({
    title: property.property_title || bookingPropertyTitle(property) || `Property #${property.id}`,
    value: property.id,
  }))

  const currentId = form.property_id ?? bookings.current?.property_id ?? bookings.current?.property?.id
  if (currentId != null && !options.some(option => String(option.value) === String(currentId))) {
    options.unshift({
      title: bookingPropertyTitle(bookings.current) || `Property #${currentId}`,
      value: currentId,
    })
  }

  return options
})

const parentBookingOptions = computed(() => {
  const currentId = String(route.params.id)
  const data = bookings.data?.data || bookings.data || []
  const list = Array.isArray(data) ? data : []

  return [
    { title: 'No it is individual booking', value: '' },
    ...list
      .filter(booking => String(booking.id) !== currentId)
      .map(booking => ({
        title: `${booking.booking_code || `#${booking.id}`} — ${booking.guest_name || 'Guest'}`,
        value: String(booking.id),
      })),
  ]
})

const payments = computed(() => {
  const list = bookings.current?.payments
  return Array.isArray(list) ? list : []
})

const paymentsTotal = computed(() => {
  if (bookings.current?.payments_total != null)
    return moneyNumber(bookings.current.payments_total)

  return payments.value.reduce((sum, payment) => sum + moneyNumber(payment.price), 0)
})

onMounted(async () => {
  await Promise.all([
    properties.load(),
    bookings.load(),
    bookings.loadOne(route.params.id),
  ])

  Object.assign(form, bookingFormFromRecord(bookings.current))
  loaded.value = true
})

const onFormUpdate = value => {
  Object.assign(form, value)
}

const save = async () => {
  try {
    await bookings.save(route.params.id, { ...form })
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Edit Booking"
      :subtitle="form.booking_code ? `Booking ${form.booking_code}` : 'Update reservation details'"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Back to list"
          to="/admin/bookings"
        />
      </template>
    </PageHeader>

    <VCard>
      <VCardItem>
        <VCardTitle>Edit Booking</VCardTitle>
        <template #append>
          <BaseButton
            v-if="form.month && form.year"
            size="small"
            variant="tonal"
            label="View all Bookings in this month/year"
            :to="{ path: '/admin/bookings', query: { month: form.month, year: form.year } }"
          />
        </template>
      </VCardItem>
      <VCardText>
        <AppAlert :errors="bookings.errors" />

        <div
          v-if="!loaded || bookings.loading"
          class="text-medium-emphasis py-8 text-center"
        >
          Loading booking…
        </div>

        <BookingForm
          v-else
          :model-value="form"
          mode="edit"
          submit-label="Update"
          :errors="bookings.errors"
          :loading="bookings.loading"
          :property-options="propertyOptions"
          :parent-booking-options="parentBookingOptions"
          :payments="payments"
          :payments-total="paymentsTotal"
          @update:model-value="onFormUpdate"
          @submit="save"
          @cancel="navigateTo('/admin/bookings')"
        />
      </VCardText>
    </VCard>
  </div>
</template>
