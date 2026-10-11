<script setup>
import { emptyBookingForm } from '@/constants/bookings'
import { bookingPropertyTitle } from '@/mocks/bookings'

const bookings = useBookingsStore()
const properties = usePropertiesStore()

const form = reactive(emptyBookingForm('create'))

const propertyOptions = computed(() => {
  const data = properties.data?.data || properties.data || []
  const list = Array.isArray(data) ? data : []

  return list.map(property => ({
    title: property.property_title || bookingPropertyTitle(property) || `Property #${property.id}`,
    value: property.id,
  }))
})

onMounted(() => properties.load())

const onFormUpdate = value => {
  Object.assign(form, value)
}

const submit = async () => {
  try {
    const selectedProperty = propertyOptions.value.find(
      option => String(option.value) === String(form.property_id),
    )

    const payload = {
      ...form,
      guests: form.guest_ids,
      property_title: selectedProperty?.title,
      property: form.property_id
        ? {
            id: form.property_id,
            property_title: selectedProperty?.title || `Property #${form.property_id}`,
          }
        : null,
    }

    const booking = await bookings.create(payload)

    await navigateTo(`/admin/bookings/${booking.id}`)
  }
  catch {
    // Store exposes validation errors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Add new Booking"
      subtitle="Reservations / Bookings"
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
        <VCardTitle>Add new Booking</VCardTitle>
      </VCardItem>
      <VCardText>
        <AppAlert :errors="bookings.errors" />
        <BookingForm
          :model-value="form"
          mode="create"
          submit-label="Save"
          :errors="bookings.errors"
          :loading="bookings.loading"
          :property-options="propertyOptions"
          @update:model-value="onFormUpdate"
          @submit="submit"
          @cancel="navigateTo('/admin/bookings')"
        />
      </VCardText>
    </VCard>
  </div>
</template>
