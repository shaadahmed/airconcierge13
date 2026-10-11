<script setup>
import {
  ADMINISTRATIVE_FEE_CATEGORIES,
  categoryShowsAdministrativeFee,
  categoryShowsConvenienceFee,
  computePaymentTotal,
  EXPENSE_PAYMENT_METHOD_OPTIONS,
  PAYMENT_TYPE_CATEGORY_OPTIONS,
  RECURRING_MONTHS_OPTIONS,
} from '@/constants/payments'
import { MOCK_BOOKINGS } from '@/mocks/bookings'
import {
  MOCK_PURCHASERS,
  MOCK_VENDORS,
  paymentMoney,
} from '@/mocks/payments'
import { MOCK_PROPERTIES } from '@/mocks/properties'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  loading: Boolean,
  mode: {
    type: String,
    default: 'create',
  },
  submitLabel: {
    type: String,
    default: 'Save payment',
  },
})

const emit = defineEmits(['update:modelValue', 'submit', 'cancel'])

const form = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const updateField = (key, value) => {
  form.value = { ...form.value, [key]: value }
}

const propertyItems = [
  { title: 'Select property', value: null },
  ...MOCK_PROPERTIES.map(property => ({
    title: property.property_title,
    value: property.id,
  })),
]

const bookingItems = computed(() => {
  const propertyId = form.value.property_id

  const bookings = MOCK_BOOKINGS.filter(booking => {
    if (propertyId == null)
      return true

    return Number(booking.property_id) === Number(propertyId)
  })

  return [
    { title: 'Select booking', value: null },
    ...bookings.map(booking => ({
      title: `${booking.booking_code || `#${booking.id}`} · ${booking.guest_name || 'Guest'}`,
      value: booking.id,
    })),
  ]
})

const vendorItems = [
  { title: 'Select vendor', value: null },
  ...MOCK_VENDORS,
  { title: 'Add New Vendor', value: '__new__' },
]

const methodItems = EXPENSE_PAYMENT_METHOD_OPTIONS
const categoryItems = PAYMENT_TYPE_CATEGORY_OPTIONS
const purchaserItems = MOCK_PURCHASERS
const recurringMonthItems = RECURRING_MONTHS_OPTIONS

const showConvenienceFee = computed(() => categoryShowsConvenienceFee(form.value.payment_type_category))
const showAdministrativeFee = computed(() => categoryShowsAdministrativeFee(form.value.payment_type_category))

const totalPrice = computed(() => computePaymentTotal({
  initialPrice: form.value.initial_price,
  convenienceFee: form.value.convenience_fee,
  managementMarkup: form.value.management_markup,
  category: form.value.payment_type_category,
}))

const administrativeFeeAmount = computed(() => {
  if (!showAdministrativeFee.value)
    return 0

  const base = Number(form.value.initial_price) || 0
  const markup = Number(form.value.management_markup) || 0

  return Number(((base * markup) / 100).toFixed(2))
})

const effectiveMonthsLabel = computed(() => {
  if (!form.value.is_recurring || !form.value.expense_incurred_date)
    return ''

  const start = new Date(form.value.expense_incurred_date)
  if (Number.isNaN(start.getTime()))
    return ''

  const months = Number(form.value.recurring_months) || 1
  const labels = []

  for (let index = 0; index < months; index += 1) {
    const date = new Date(start.getFullYear(), start.getMonth() + index, 1)

    labels.push(date.toLocaleString(undefined, { month: 'short', year: 'numeric' }))
  }

  return labels.join(', ')
})

watch(
  () => form.value.property_payment_type,
  method => {
    const option = methodItems.find(item => item.value === method)
    if (!option)
      return

    if (showConvenienceFee.value)
      updateField('convenience_fee', option.convenience_fee ?? 0)
    if (showAdministrativeFee.value)
      updateField('management_markup', option.management_markup ?? 0)
  },
)

watch(
  () => form.value.payment_type_category,
  category => {
    if (categoryShowsConvenienceFee(category)) {
      updateField('management_markup', 0)

      const option = methodItems.find(item => item.value === form.value.property_payment_type)

      updateField('convenience_fee', option?.convenience_fee ?? form.value.convenience_fee ?? 0)
    }
    else if (ADMINISTRATIVE_FEE_CATEGORIES.includes(category)) {
      updateField('convenience_fee', 0)

      const option = methodItems.find(item => item.value === form.value.property_payment_type)

      updateField('management_markup', option?.management_markup ?? form.value.management_markup ?? 0)
    }
    else {
      updateField('convenience_fee', 0)
      updateField('management_markup', 0)
    }
  },
)

watch(totalPrice, value => {
  if (Number(form.value.price) !== value)
    updateField('price', value)
})

watch(
  () => form.value.against_property,
  againstProperty => {
    if (againstProperty)
      updateField('booking_id', null)
  },
)

const onSubmit = () => emit('submit')
</script>

<template>
  <VForm @submit.prevent="onSubmit">
    <AppAlert :errors="errors" />

    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.property_id"
          label="Property"
          :items="propertyItems"
          :error="errors.property_id"
          required
          @update:model-value="updateField('property_id', $event)"
        />
      </VCol>
      <VCol
        cols="12"
        md="6"
        class="d-flex align-end"
      >
        <BaseCheckbox
          :model-value="form.against_property"
          label="Not related to a specific reservation"
          hide-details
          class="mb-4"
          @update:model-value="updateField('against_property', $event)"
        />
      </VCol>

      <VCol
        v-if="!form.against_property"
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.booking_id"
          label="Booking"
          :items="bookingItems"
          :error="errors.booking_id"
          required
          @update:model-value="updateField('booking_id', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.vendor_name"
          label="Vendor Name"
          :items="vendorItems"
          :error="errors.vendor_name"
          required
          @update:model-value="updateField('vendor_name', $event)"
        />
      </VCol>

      <VCol
        v-if="form.vendor_name === '__new__'"
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.other_vendor_name"
          label="New vendor name"
          :error="errors.other_vendor_name"
          required
          @update:model-value="updateField('other_vendor_name', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.payment_type_category"
          label="Expense/Charge Category"
          :items="categoryItems"
          :error="errors.payment_type_category"
          required
          @update:model-value="updateField('payment_type_category', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.payment_type_title"
          label="Expense/Charge Title"
          :error="errors.payment_type_title"
          placeholder="Short description"
          @update:model-value="updateField('payment_type_title', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.expense_incurred_date"
          label="Purchase / Expense Incurred Date"
          type="date"
          hint="Determines the mm/yyyy charged to the owner"
          persistent-hint
          :error="errors.expense_incurred_date"
          required
          @update:model-value="updateField('expense_incurred_date', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.property_payment_type"
          label="Payment Type"
          :items="methodItems"
          :error="errors.property_payment_type"
          required
          @update:model-value="updateField('property_payment_type', $event)"
        />
      </VCol>

      <VCol
        v-if="showConvenienceFee"
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.convenience_fee"
          label="Convenience Fee"
          type="number"
          :error="errors.convenience_fee"
          @update:model-value="updateField('convenience_fee', $event)"
        />
      </VCol>

      <VCol
        v-if="showAdministrativeFee"
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.management_markup"
          label="Administrative Fee (%)"
          type="number"
          :hint="`Fee amount: ${paymentMoney(administrativeFeeAmount)}`"
          persistent-hint
          :error="errors.management_markup"
          @update:model-value="updateField('management_markup', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.initial_price"
          label="Price"
          type="number"
          :error="errors.initial_price"
          required
          @update:model-value="updateField('initial_price', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.price"
          label="Total Price"
          type="number"
          readonly
          :error="errors.price"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.purchaser_id"
          label="Purchaser Name"
          :items="purchaserItems"
          hint="Who at Air Concierge made this payment?"
          persistent-hint
          :error="errors.purchaser_id"
          required
          @update:model-value="updateField('purchaser_id', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <VFileInput
          :model-value="form.receipt_files"
          label="Receipt"
          accept="image/*,.pdf"
          multiple
          prepend-icon="bx-paperclip"
          variant="outlined"
          hint="Images or PDF"
          persistent-hint
          @update:model-value="updateField('receipt_files', $event)"
        />
      </VCol>

      <VCol cols="12">
        <BaseTextarea
          :model-value="form.notes"
          label="Notes"
          :error="errors.notes"
          @update:model-value="updateField('notes', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseCheckbox
          :model-value="form.is_recurring"
          label="Recurring Payment"
          hide-details
          class="mb-2"
          @update:model-value="updateField('is_recurring', $event)"
        />
        <BaseSelect
          v-if="form.is_recurring"
          :model-value="form.recurring_months"
          label="Repeat for next (months)"
          :items="recurringMonthItems"
          :hint="effectiveMonthsLabel ? `Effective months: ${effectiveMonthsLabel}` : 'Set expense date first'"
          persistent-hint
          @update:model-value="updateField('recurring_months', $event)"
        />
      </VCol>

      <VCol
        v-if="mode === 'edit' && form.is_recurring"
        cols="12"
        md="6"
      >
        <BaseCheckbox
          :model-value="form.end_recurring_chain"
          label="End recurring payment chain on this record"
          hide-details
          @update:model-value="updateField('end_recurring_chain', $event)"
        />
      </VCol>
    </VRow>

    <div class="d-flex flex-wrap gap-2 mt-4">
      <BaseButton
        type="submit"
        color="primary"
        :label="submitLabel"
        :loading="loading"
      />
      <BaseButton
        type="button"
        variant="tonal"
        label="Cancel"
        @click="emit('cancel')"
      />
    </div>
  </VForm>
</template>
