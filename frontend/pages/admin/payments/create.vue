<script setup>
import CostExpenseForm from '@/components/payments/CostExpenseForm.vue'
import { computePaymentTotal } from '@/constants/payments'
import { MOCK_PURCHASERS } from '@/mocks/payments'
import { MOCK_PROPERTIES } from '@/mocks/properties'
import { MOCK_BOOKINGS } from '@/mocks/bookings'

const payments = usePaymentsStore()

const emptyForm = () => ({
  property_id: null,
  against_property: false,
  booking_id: null,
  vendor_name: null,
  other_vendor_name: '',
  payment_type_category: null,
  payment_type_title: '',
  expense_incurred_date: '',
  property_payment_type: null,
  convenience_fee: 0,
  management_markup: 0,
  initial_price: '',
  price: 0,
  purchaser_id: null,
  receipt_files: [],
  notes: '',
  is_recurring: false,
  recurring_months: 1,
  end_recurring_chain: false,
})

const form = ref(emptyForm())

const resolveVendorName = () => {
  if (form.value.vendor_name === '__new__')
    return form.value.other_vendor_name?.trim() || 'New vendor'

  return form.value.vendor_name
}

const resolvePurchaserName = () => {
  const match = MOCK_PURCHASERS.find(item => item.value === form.value.purchaser_id)

  return match?.title || null
}

const resolvePropertyTitle = () => {
  const match = MOCK_PROPERTIES.find(item => item.id === form.value.property_id)

  return match?.property_title || `Property #${form.value.property_id}`
}

const resolveBookingMeta = () => {
  const booking = MOCK_BOOKINGS.find(item => item.id === form.value.booking_id)
  if (!booking)
    return {}

  return {
    booking_code: booking.booking_code,
    guest_name: booking.guest_name,
    reservation_start_date: booking.reservation_start_date,
    reservation_end_date: booking.reservation_end_date,
  }
}

const buildPayload = () => {
  const vendorName = resolveVendorName()
  const expenseDate = form.value.expense_incurred_date
  const date = expenseDate ? new Date(expenseDate) : null

  const total = computePaymentTotal({
    initialPrice: form.value.initial_price,
    convenienceFee: form.value.convenience_fee,
    managementMarkup: form.value.management_markup,
    category: form.value.payment_type_category,
  })

  return {
    property_id: form.value.property_id,
    property_title: resolvePropertyTitle(),
    booking_id: form.value.against_property ? null : form.value.booking_id,
    ...(!form.value.against_property ? resolveBookingMeta() : {}),
    vendor_name: vendorName,
    payment_type_category: form.value.payment_type_category,
    payment_type_title: form.value.payment_type_title || form.value.payment_type_category,
    expense_incurred_date: expenseDate || null,
    payment_date: expenseDate || null,
    payment_status: 'Paid',
    property_payment_type: form.value.property_payment_type,
    purchaser_id: form.value.purchaser_id,
    purchaser_name: resolvePurchaserName(),
    initial_price: Number(form.value.initial_price) || 0,
    convenience_fee: Number(form.value.convenience_fee) || 0,
    management_markup: Number(form.value.management_markup) || 0,
    price: total,
    amount: total,
    notes: form.value.notes || null,
    is_recurring: Boolean(form.value.is_recurring),
    recurring_months: form.value.is_recurring ? Number(form.value.recurring_months) || 1 : null,
    receipt_url: null,
    year: date && !Number.isNaN(date.getTime()) ? date.getFullYear() : null,
    month: date && !Number.isNaN(date.getTime()) ? date.getMonth() + 1 : null,
  }
}

const submit = async () => {
  try {
    const payload = buildPayload()

    if (form.value.against_property || !form.value.booking_id)
      await payments.createPropertyPayment(payload)
    else
      await payments.create(payload)

    await navigateTo('/admin/payments/bookings')
  }
  catch {
    // Store exposes validation errors.
  }
}

const cancel = () => navigateTo('/admin/payments/bookings')

onMounted(() => payments.loadAll())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Add cost / expense"
      subtitle="Create a booking or property payment (legacy Payments create)"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Back to list"
          prepend-icon="bx-arrow-back"
          @click="cancel"
        />
      </template>
    </PageHeader>

    <VCard>
      <VCardText>
        <CostExpenseForm
          v-model="form"
          :errors="payments.errors"
          :loading="payments.loading"
          submit-label="Create payment"
          @submit="submit"
          @cancel="cancel"
        />
      </VCardText>
    </VCard>
  </div>
</template>
