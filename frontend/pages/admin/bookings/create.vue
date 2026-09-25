<script setup>
const bookings = useBookingsStore()
const properties = usePropertiesStore()

const form = reactive({
  property_id: null,
  platform_id: '',
  booking_code: '',
  reservation_start_date: '',
  reservation_end_date: '',
  booking_date: '',
  no_of_guests: '',
  owner_notes: '',
  booking_notes: '',
})

const propertyOptions = computed(() => {
  const data = properties.data?.data || properties.data || []
  const list = Array.isArray(data) ? data : []

  return list.map(property => ({
    title: property.property_title || `Property #${property.id}`,
    value: property.id,
  }))
})

onMounted(() => properties.load())

const submit = async () => {
  try {
    const payload = {
      property_id: form.property_id,
      platform_id: form.platform_id || null,
      booking_code: form.booking_code || null,
      reservation_start_date: form.reservation_start_date || null,
      reservation_end_date: form.reservation_end_date || null,
      booking_date: form.booking_date || null,
      no_of_guests: form.no_of_guests === '' ? null : Number(form.no_of_guests),
      owner_notes: form.owner_notes || null,
      booking_notes: form.booking_notes || null,
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
      title="Create booking"
      subtitle="Add a reservation"
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
      <VCardText>
        <AppAlert :errors="bookings.errors" />
        <VForm @submit.prevent="submit">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <BaseSelect
                v-model="form.property_id"
                label="Property"
                :items="propertyOptions"
                :error="bookings.errors.property_id"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <BaseInput
                v-model="form.booking_code"
                label="Booking code"
                :error="bookings.errors.booking_code"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <BaseInput
                v-model="form.platform_id"
                label="Platform ID"
                type="number"
                :error="bookings.errors.platform_id"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <BaseInput
                v-model="form.no_of_guests"
                label="Number of guests"
                type="number"
                :error="bookings.errors.no_of_guests"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <BaseInput
                v-model="form.booking_date"
                label="Booking date"
                type="date"
                :error="bookings.errors.booking_date"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <BaseInput
                v-model="form.reservation_start_date"
                label="Reservation start"
                type="date"
                :error="bookings.errors.reservation_start_date"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <BaseInput
                v-model="form.reservation_end_date"
                label="Reservation end"
                type="date"
                :error="bookings.errors.reservation_end_date"
              />
            </VCol>
            <VCol cols="12">
              <BaseTextarea
                v-model="form.owner_notes"
                label="Owner notes"
                :error="bookings.errors.owner_notes"
              />
            </VCol>
            <VCol cols="12">
              <BaseTextarea
                v-model="form.booking_notes"
                label="Booking notes"
                :error="bookings.errors.booking_notes"
              />
            </VCol>
          </VRow>

          <div class="d-flex flex-wrap gap-2 mt-4">
            <BaseButton
              type="submit"
              label="Create booking"
              :loading="bookings.loading"
            />
            <BaseButton
              type="button"
              variant="tonal"
              label="Cancel"
              to="/admin/bookings"
            />
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
