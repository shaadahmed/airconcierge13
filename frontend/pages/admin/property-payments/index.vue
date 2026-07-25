<script setup>
const payments = usePaymentsStore()
const form = reactive({ property_id: '', amount: '', payment_date: '', notes: '' })

const rows = computed(() => {
  const data = payments.propertyPayments?.data || payments.propertyPayments || []

  return Array.isArray(data) ? data : []
})

onMounted(() => payments.loadPropertyPayments())

const submit = async () => {
  try {
    await payments.createPropertyPayment(form)
    Object.assign(form, { property_id: '', amount: '', payment_date: '', notes: '' })
    await payments.loadPropertyPayments()
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
      title="Property payments"
      subtitle="Payments tied to properties"
    />

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard title="Add property payment">
          <VCardText>
            <AppAlert :errors="payments.errors" />
            <VForm @submit.prevent="submit">
              <BaseInput
                v-model="form.property_id"
                label="Property ID"
                :error="payments.errors.property_id"
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
          title="Recent property payments"
          :loading="payments.loading"
          :empty="rows.length === 0"
          :empty-colspan="4"
          empty-title="No property payments found"
        >
          <template #head>
            <thead>
              <tr>
                <th>ID</th>
                <th>Property</th>
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
            <td>{{ payment.property_id }}</td>
            <td>{{ payment.amount }}</td>
            <td>{{ payment.payment_date || '—' }}</td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>
  </div>
</template>
