<script setup>
const route = useRoute()
const bookings = useBookingsStore()
const form = reactive({
  booking_code: '',
  reservation_start_date: '',
  reservation_end_date: '',
  no_of_guests: null,
  owner_notes: '',
  booking_notes: '',
})

onMounted(async () => {
  await bookings.loadOne(route.params.id)
  Object.keys(form).forEach(key => {
    form[key] = bookings.current?.[key] ?? form[key]
  })
})

const save = async () => {
  try {
    await bookings.save(route.params.id, form)
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard title="Booking details">
    <VCardText>
      <VAlert
        v-if="bookings.errors.general"
        type="error"
        class="mb-4"
      >
        {{ bookings.errors.general[0] }}
      </VAlert>
      <VForm @submit.prevent="save">
        <VRow>
          <VCol
            v-for="field in ['booking_code', 'reservation_start_date', 'reservation_end_date', 'no_of_guests']"
            :key="field"
            cols="12"
            md="6"
          >
            <BaseInput
              v-model="form[field]"
              :label="field.replaceAll('_', ' ')"
              :type="field.includes('date') ? 'date' : 'text'"
              :error="bookings.errors[field]"
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
        <BaseButton
          type="submit"
          label="Save booking"
          :loading="bookings.loading"
        />
      </VForm>
    </VCardText>
  </VCard>
</template>
