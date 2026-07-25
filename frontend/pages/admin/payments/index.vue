<script setup>
const payments = usePaymentsStore()
const form = reactive({ booking_id: '', amount: '', payment_date: '', notes: '' })

const rows = computed(() => {
  const data = payments.data?.data || payments.data || []

  return Array.isArray(data) ? data : []
})

onMounted(() => payments.load())

const submit = async () => {
  try {
    await payments.create(form)
    Object.assign(form, { booking_id: '', amount: '', payment_date: '', notes: '' })
    await payments.load()
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
      title="Payments"
      subtitle="Guest and owner payment records"
    />

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard title="Add payment">
          <VCardText>
            <AppAlert :errors="payments.errors" />
            <VForm @submit.prevent="submit">
              <BaseInput
                v-model="form.booking_id"
                label="Booking ID"
                :error="payments.errors.booking_id"
              />
              <BaseInput
                v-model="form.amount"
                label="Amount"
                type="number"
                :error="payments.errors.amount"
              />
              <BaseInput
                v-model="form.payment_date"
                label="Payment date"
                type="date"
                :error="payments.errors.payment_date"
              />
              <BaseTextarea
                v-model="form.notes"
                label="Notes"
                :error="payments.errors.notes"
              />
              <BaseButton
                type="submit"
                label="Create payment"
                :loading="payments.loading"
              />
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="Recent payments"
          :loading="payments.loading"
          :empty="rows.length === 0"
          :empty-colspan="4"
          empty-title="No payments found"
        >
          <template #head>
            <thead>
              <tr>
                <th>ID</th>
                <th>Booking</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="payment in rows"
            :key="payment.id"
          >
            <td>{{ payment.id }}</td>
            <td>{{ payment.booking_id }}</td>
            <td>{{ payment.amount }}</td>
            <td>{{ payment.payment_date || '—' }}</td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>
  </div>
</template>
