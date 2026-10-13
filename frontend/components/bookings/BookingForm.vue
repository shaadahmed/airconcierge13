<script setup>
import { findMockProperty } from '@/mocks/properties'
import {
  AIRCONCIERGE_PAID_FIELDS,
  BOOKING_CANCELLED_OPTIONS,
  BOOKING_DISPUTE_STATUS_OPTIONS,
  BOOKING_GUEST_OPTIONS,
  BOOKING_MONTH_OPTIONS,
  BOOKING_PLATFORM_OPTIONS,
  BOOKING_STAFF_OPTIONS,
  BOOKING_TOT_MODE_CREATE_OPTIONS,
  BOOKING_TOT_MODE_EDIT_OPTIONS,
  BOOKING_YEAR_OPTIONS,
  formatMoney,
  GUEST_PAID_FIELDS,
  moneyNumber,
  nightsBetween,
} from '@/constants/bookings'

const props = defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  loading: Boolean,
  mode: { type: String, default: 'create' },
  submitLabel: { type: String, default: 'Save' },
  propertyOptions: { type: Array, default: () => [] },
  parentBookingOptions: { type: Array, default: () => [] },
  payments: { type: Array, default: () => [] },
  paymentsTotal: { type: [Number, String], default: 0 },
})

const emit = defineEmits(['update:modelValue', 'submit', 'cancel'])

const form = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const isEdit = computed(() => props.mode === 'edit')
const isCohost = computed(() => String(form.value.is_cohost_booking) === '1')

const showElectToRemit = computed(() => form.value.tot_mode === 'paid_at_owners_election')
const showAcToCity = computed(() => form.value.tot_mode === 'paid_by_airconcierge_to_city')

const guestOptions = BOOKING_GUEST_OPTIONS
const staffOptions = BOOKING_STAFF_OPTIONS
const platformOptions = BOOKING_PLATFORM_OPTIONS
const monthOptions = BOOKING_MONTH_OPTIONS
const yearOptions = BOOKING_YEAR_OPTIONS
const cancelledOptions = BOOKING_CANCELLED_OPTIONS
const disputeOptions = BOOKING_DISPUTE_STATUS_OPTIONS
const totCreateOptions = BOOKING_TOT_MODE_CREATE_OPTIONS
const totEditOptions = BOOKING_TOT_MODE_EDIT_OPTIONS

const nights = computed(() => moneyNumber(form.value.no_of_nights))

const safelyGuestCharge = computed(() => formatMoney(moneyNumber(form.value.safely_guest_rate) * nights.value))
const safelyCost = computed(() => formatMoney(moneyNumber(form.value.safely_cost_rate) * nights.value))
const safelyRevenue = computed(() => formatMoney(moneyNumber(form.value.safely_revenue_rate) * nights.value))

const paymentsTotalLabel = computed(() => `$${formatMoney(props.paymentsTotal)}`)

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value,
  })
}

const patchForm = patch => {
  emit('update:modelValue', {
    ...props.modelValue,
    ...patch,
  })
}

const fieldError = key => props.errors?.[key]

const sumFrom = (source, fields) => {
  let total = 0
  for (const field of fields) {
    if (!isEdit.value && (field === 'safely_insurance_fee' || field === 'credit_card_transaction_fee'))
      continue
    if (field === 'total_paid_by_airconcierge_to_city' && source.tot_mode !== 'paid_by_airconcierge_to_city')
      continue

    total += moneyNumber(source[field])
  }

  return total
}

const guestPaidFields = () => (isEdit.value
  ? GUEST_PAID_FIELDS
  : GUEST_PAID_FIELDS.filter(field => field !== 'safely_insurance_fee'))

const airconciergePaidFields = () => (isEdit.value
  ? AIRCONCIERGE_PAID_FIELDS
  : AIRCONCIERGE_PAID_FIELDS.filter(field =>
    field !== 'credit_card_transaction_fee' && field !== 'safely_insurance_fee',
  ))

const recalculateGuestTotal = () => {
  if (form.value.disable_autocalculation)
    return

  patchForm({ total_guest_paid: formatMoney(sumFrom(form.value, guestPaidFields())) })
}

const recalculateAirconciergeTotal = () => {
  if (form.value.disable_autocalculation)
    return

  patchForm({
    total_airconcierge_paid_out: formatMoney(sumFrom(form.value, airconciergePaidFields())),
  })
}

const recalculateManagementFee = () => {
  if (form.value.disable_autocalculation)
    return

  const rate = moneyNumber(form.value.ac_management_fee)
  const base
    = moneyNumber(form.value.accomodations)
    + moneyNumber(form.value.pet_fee)
    + moneyNumber(form.value.daily_utility_fee)

  const managementFee = base * (rate / 100)
  const staffBonus = managementFee * (rate / 100)

  const next = {
    ...form.value,
    management_fee: managementFee > 0 ? formatMoney(managementFee) : '',
    staff_commession: managementFee > 0 ? formatMoney(staffBonus) : '',
    total_guest_paid: formatMoney(sumFrom(form.value, guestPaidFields())),
  }

  next.total_airconcierge_paid_out = formatMoney(sumFrom(next, airconciergePaidFields()))
  emit('update:modelValue', next)
}

const onPropertyChange = value => {
  const property = findMockProperty(value)
  const fee = property?.ac_management_fee != null ? String(property.ac_management_fee) : '0'
  const cohost = property?.property_cohost != null ? String(property.property_cohost) : '0'

  const next = {
    ...form.value,
    property_id: value,
    ac_management_fee: fee,
    is_cohost_booking: cohost,
  }

  if (!form.value.disable_autocalculation) {
    const rate = moneyNumber(fee)
    const base
      = moneyNumber(next.accomodations)
      + moneyNumber(next.pet_fee)
      + moneyNumber(next.daily_utility_fee)
    const managementFee = base * (rate / 100)
    const staffBonus = managementFee * (rate / 100)

    next.management_fee = managementFee > 0 ? formatMoney(managementFee) : ''
    next.staff_commession = managementFee > 0 ? formatMoney(staffBonus) : ''
    next.total_guest_paid = formatMoney(sumFrom(next, guestPaidFields()))
    next.total_airconcierge_paid_out = formatMoney(sumFrom(next, airconciergePaidFields()))
  }

  emit('update:modelValue', next)
}

const onDatesChange = (field, value) => {
  const next = {
    ...props.modelValue,
    [field]: value,
  }

  if (!form.value.disable_autocalculation) {
    const nightsCount = nightsBetween(
      field === 'reservation_start_date' ? value : form.value.reservation_start_date,
      field === 'reservation_end_date' ? value : form.value.reservation_end_date,
    )
    next.no_of_nights = nightsCount ? String(nightsCount) : '0'
  }

  emit('update:modelValue', next)
}

const onCleaningFeeInput = value => {
  if (form.value.disable_autocalculation) {
    updateField('cleaning_fee', value)

    return
  }

  const next = {
    ...form.value,
    cleaning_fee: value,
  }

  if (!isEdit.value)
    next.payment_of_cleaners = value

  next.total_guest_paid = formatMoney(sumFrom(next, guestPaidFields()))
  next.total_airconcierge_paid_out = formatMoney(sumFrom(next, airconciergePaidFields()))
  emit('update:modelValue', next)
}

const onGuestPaidBlur = field => {
  recalculateGuestTotal()
  if (['accomodations', 'pet_fee', 'daily_utility_fee'].includes(field))
    recalculateManagementFee()
}

const onTotModeChange = value => {
  const next = {
    ...form.value,
    tot_mode: value,
  }

  if (!form.value.disable_autocalculation) {
    const charged = form.value.tot_charged_to_guest || '0.00'
    if (value === 'paid_at_owners_election') {
      next.total_elect_to_remit = charged
      next.total_paid_by_airconcierge_to_city = '0.00'
    }
    else if (value === 'paid_by_airconcierge_to_city') {
      next.total_paid_by_airconcierge_to_city = charged
      next.total_elect_to_remit = '0.00'
    }

    next.total_airconcierge_paid_out = formatMoney(sumFrom(next, airconciergePaidFields()))
  }

  emit('update:modelValue', next)
}

const onTotChargedChange = value => {
  const next = {
    ...form.value,
    tot_charged_to_guest: value,
  }

  if (!form.value.disable_autocalculation) {
    if (form.value.tot_mode === 'paid_at_owners_election')
      next.total_elect_to_remit = value
    else if (form.value.tot_mode === 'paid_by_airconcierge_to_city')
      next.total_paid_by_airconcierge_to_city = value

    next.total_guest_paid = formatMoney(sumFrom(next, guestPaidFields()))
    next.total_airconcierge_paid_out = formatMoney(sumFrom(next, airconciergePaidFields()))
  }

  emit('update:modelValue', next)
}

const onGuestSelect = value => {
  if (isEdit.value) {
    const guest = guestOptions.find(option => String(option.value) === String(value))
    patchForm({
      guest_id: value,
      guest_name: guest?.title || form.value.guest_name,
      phone: guest?.phone || form.value.phone,
      email: guest?.email || form.value.email,
    })

    return
  }

  const selected = Array.isArray(value) ? value : []
  const lastId = selected[selected.length - 1]
  const guest = guestOptions.find(option => String(option.value) === String(lastId))

  patchForm({
    guest_ids: selected,
    guest_name: guest?.title || '',
  })
}

const onManagementFeeBlur = () => {
  if (form.value.disable_autocalculation)
    return

  const managementFee = moneyNumber(form.value.management_fee)
  const rate = moneyNumber(form.value.ac_management_fee)
  const staffBonus = managementFee * (rate / 100)

  const next = {
    ...form.value,
    staff_commession: managementFee > 0 ? formatMoney(staffBonus) : '',
  }

  next.total_airconcierge_paid_out = formatMoney(sumFrom(next, airconciergePaidFields()))
  emit('update:modelValue', next)
}
</script>

<template>
  <VForm @submit.prevent="$emit('submit')">
    <VAlert
      v-if="isCohost"
      type="info"
      variant="tonal"
      class="mb-4"
      density="comfortable"
    >
      This property is Co-Host on Owner's Profile
    </VAlert>

    <VRow>
      <!-- Property -->
      <VCol cols="12">
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Property
        </div>
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.property_id"
          label="Property"
          :items="propertyOptions"
          clearable
          required
          :error="fieldError('property_id')"
          @update:model-value="onPropertyChange"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.ac_management_fee"
          label="Management Fee (%)"
          readonly
          :error="fieldError('ac_management_fee')"
          @update:model-value="updateField('ac_management_fee', $event)"
        />
      </VCol>

      <!-- Booking -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Booking
        </div>
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.month"
          label="Month"
          :items="monthOptions"
          :required="!isEdit"
          :error="fieldError('month')"
          @update:model-value="updateField('month', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.year"
          label="Year"
          :items="yearOptions"
          :required="!isEdit"
          :error="fieldError('year')"
          @update:model-value="updateField('year', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.reservation_start_date"
          label="Reservation Start Date"
          type="date"
          required
          :error="fieldError('reservation_start_date')"
          @update:model-value="onDatesChange('reservation_start_date', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.reservation_end_date"
          label="Reservation End Date"
          type="date"
          :required="!isEdit"
          :error="fieldError('reservation_end_date')"
          @update:model-value="onDatesChange('reservation_end_date', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.no_of_nights"
          label="No of Nights"
          type="number"
          :error="fieldError('no_of_nights')"
          @update:model-value="updateField('no_of_nights', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.booking_date"
          label="Booking Date"
          type="date"
          required
          :error="fieldError('booking_date')"
          @update:model-value="updateField('booking_date', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          v-if="isEdit"
          :model-value="form.guest_id"
          label="Guest"
          :items="guestOptions"
          clearable
          required
          :error="fieldError('guest_id')"
          @update:model-value="onGuestSelect"
        />
        <BaseSelect
          v-else
          :model-value="form.guest_ids"
          label="Guest"
          :items="guestOptions"
          multiple
          chips
          closable-chips
          required
          :error="fieldError('guest_ids') || fieldError('guests')"
          @update:model-value="onGuestSelect"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.no_of_guests"
          label="No of Guests"
          required
          :error="fieldError('no_of_guests')"
          @update:model-value="updateField('no_of_guests', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        :md="isEdit ? 6 : 6"
      >
        <BaseSelect
          :model-value="form.platform_id"
          label="Platform"
          :items="platformOptions"
          clearable
          required
          :error="fieldError('platform_id')"
          @update:model-value="updateField('platform_id', $event)"
        />
      </VCol>

      <template v-if="!isEdit">
        <VCol
          cols="12"
          md="6"
        >
          <BaseSelect
            :model-value="form.tot_mode"
            label="TOT (Transient Occupancy Tax) Mode"
            :items="totCreateOptions"
            clearable
            required
            :error="fieldError('tot_mode')"
            @update:model-value="onTotModeChange"
          />
        </VCol>
      </template>

      <template v-else>
        <VCol
          cols="12"
          md="4"
        >
          <BaseSelect
            :model-value="form.cancelled_booking"
            label="Cancelled Booking"
            :items="cancelledOptions"
            clearable
            :error="fieldError('cancelled_booking')"
            @update:model-value="updateField('cancelled_booking', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          md="2"
        >
          <BaseSelect
            :model-value="form.is_payment_clear"
            label="Dispute Status"
            :items="disputeOptions"
            clearable
            :error="fieldError('is_payment_clear')"
            @update:model-value="updateField('is_payment_clear', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          md="6"
        >
          <BaseInput
            :model-value="form.booking_code"
            label="Booking Code"
            required
            :error="fieldError('booking_code')"
            @update:model-value="updateField('booking_code', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          md="6"
        >
          <BaseSelect
            :model-value="form.parent_id"
            label="Select if this is part of a series of Bookings"
            :items="parentBookingOptions"
            clearable
            hint="Attach successive bookings to the parent reservation."
            persistent-hint
            :error="fieldError('parent_id')"
            @update:model-value="updateField('parent_id', $event)"
          />
        </VCol>

        <!-- Guest Information -->
        <VCol
          cols="12"
          class="mt-2"
        >
          <VDivider class="mb-4" />
          <div class="text-subtitle-1 font-weight-medium mb-1">
            Guest Information
          </div>
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <BaseInput
            :model-value="form.guest_name"
            label="Guest Name"
            :error="fieldError('guest_name')"
            @update:model-value="updateField('guest_name', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <BaseInput
            :model-value="form.phone"
            label="Guest Phone Number"
            :error="fieldError('phone')"
            @update:model-value="updateField('phone', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <BaseInput
            :model-value="form.email"
            label="Guest Email Address"
            :error="fieldError('email')"
            @update:model-value="updateField('email', $event)"
          />
        </VCol>

        <!-- TOT Mode radios -->
        <VCol
          cols="12"
          class="mt-2"
        >
          <VDivider class="mb-4" />
          <div class="text-subtitle-1 font-weight-medium mb-3">
            TOT Mode
          </div>
          <VRadioGroup
            :model-value="form.tot_mode"
            @update:model-value="onTotModeChange"
          >
            <VRow>
              <VCol
                v-for="option in totEditOptions"
                :key="option.value"
                cols="12"
                md="6"
              >
                <BaseRadio
                  :value="option.value"
                  :label="option.title"
                />
              </VCol>
            </VRow>
          </VRadioGroup>
        </VCol>
      </template>

      <!-- What Guest Paid -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          What Guest Paid
        </div>
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.accomodations"
          label="Accommodation"
          placeholder="0.00"
          :error="fieldError('accomodations')"
          @update:model-value="updateField('accomodations', $event)"
          @blur="onGuestPaidBlur('accomodations')"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.cleaning_fee"
          label="Cleaning Fee"
          placeholder="0.00"
          :error="fieldError('cleaning_fee')"
          @update:model-value="onCleaningFeeInput"
          @blur="recalculateGuestTotal"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.tot_charged_to_guest"
          label="TOT (Charged to Guest)"
          placeholder="0.00"
          :error="fieldError('tot_charged_to_guest')"
          @update:model-value="onTotChargedChange"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.daily_utility_fee"
          label="Resort Fee"
          placeholder="0.00"
          :error="fieldError('daily_utility_fee')"
          @update:model-value="updateField('daily_utility_fee', $event)"
          @blur="onGuestPaidBlur('daily_utility_fee')"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.security_deposit"
          label="Security Deposit Fee"
          placeholder="0.00"
          :error="fieldError('security_deposit')"
          @update:model-value="updateField('security_deposit', $event)"
          @blur="recalculateGuestTotal"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.pet_fee"
          label="Pet Fee"
          placeholder="0.00"
          :error="fieldError('pet_fee')"
          @update:model-value="updateField('pet_fee', $event)"
          @blur="onGuestPaidBlur('pet_fee')"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.airbnb_cohost_payout"
          label="Damage Claim (Income)"
          placeholder="0.00"
          :error="fieldError('airbnb_cohost_payout')"
          @update:model-value="updateField('airbnb_cohost_payout', $event)"
          @blur="recalculateGuestTotal"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.accounting_adjustment_income"
          label="Account Adjustment (Income)"
          placeholder="0.00"
          :error="fieldError('accounting_adjustment_income')"
          @update:model-value="updateField('accounting_adjustment_income', $event)"
          @blur="recalculateGuestTotal"
        />
      </VCol>

      <VCol
        v-if="isEdit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.safely_insurance_fee"
          label="Safely Insurance Fee"
          placeholder="0.00"
          :error="fieldError('safely_insurance_fee')"
          @update:model-value="updateField('safely_insurance_fee', $event)"
          @blur="() => { recalculateGuestTotal(); recalculateAirconciergeTotal() }"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.total_guest_paid"
          label="TOTAL (What Guest Paid)"
          placeholder="0.00"
          :hint="isEdit ? 'Includes safely insurance fee' : undefined"
          persistent-hint
          :error="fieldError('total_guest_paid')"
          @update:model-value="updateField('total_guest_paid', $event)"
        />
      </VCol>

      <!-- What Air Concierge Paid out -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          What Air Concierge Paid out
        </div>
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.site_listing_fee"
          label="Site Listing Fee"
          placeholder="0.00"
          :error="fieldError('site_listing_fee')"
          @update:model-value="updateField('site_listing_fee', $event)"
          @blur="recalculateAirconciergeTotal"
        />
      </VCol>

      <VCol
        v-if="isEdit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.credit_card_transaction_fee"
          label="Credit Card Processing"
          placeholder="0.00"
          :error="fieldError('credit_card_transaction_fee')"
          @update:model-value="updateField('credit_card_transaction_fee', $event)"
          @blur="recalculateAirconciergeTotal"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.management_fee"
          label="Management Fee"
          placeholder="0.00"
          :error="fieldError('management_fee')"
          @update:model-value="updateField('management_fee', $event)"
          @blur="onManagementFeeBlur"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.payment_of_cleaners"
          label="Paid to Cleaners"
          placeholder="0.00"
          :error="fieldError('payment_of_cleaners')"
          @update:model-value="updateField('payment_of_cleaners', $event)"
          @blur="recalculateAirconciergeTotal"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseSelect
          :model-value="form.air_concierge_staff"
          label="Air Concierge Staff"
          :items="staffOptions"
          clearable
          :error="fieldError('air_concierge_staff')"
          @update:model-value="updateField('air_concierge_staff', $event)"
        />
      </VCol>

      <VCol
        v-if="!isEdit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.accounting_adjustment"
          label="Account Adjustment (Expense)"
          placeholder="0.00"
          :error="fieldError('accounting_adjustment')"
          @update:model-value="updateField('accounting_adjustment', $event)"
          @blur="recalculateAirconciergeTotal"
        />
      </VCol>

      <VCol
        v-if="!isEdit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.convenience_fee"
          label="Convenience Fee"
          placeholder="0.00"
          :error="fieldError('convenience_fee')"
          @update:model-value="updateField('convenience_fee', $event)"
        />
      </VCol>

      <VCol
        v-if="!isEdit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.staff_commession"
          label="Staff Bonus"
          placeholder="0.00"
          :error="fieldError('staff_commession')"
          @update:model-value="updateField('staff_commession', $event)"
        />
      </VCol>

      <VCol
        v-if="!isEdit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.commession_offset"
          label="Staff Bonus Offset"
          placeholder="0.00"
          :error="fieldError('commession_offset')"
          @update:model-value="updateField('commession_offset', $event)"
        />
      </VCol>

      <VCol
        v-show="showElectToRemit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.total_elect_to_remit"
          label="TOT (Should you elect to remit)"
          placeholder="0.00"
          :error="fieldError('total_elect_to_remit')"
          @update:model-value="updateField('total_elect_to_remit', $event)"
        />
      </VCol>

      <VCol
        v-show="showAcToCity"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.total_paid_by_airconcierge_to_city"
          label="TOT (Paid by Air Concierge to City)"
          placeholder="0.00"
          :error="fieldError('total_paid_by_airconcierge_to_city')"
          @update:model-value="updateField('total_paid_by_airconcierge_to_city', $event)"
          @blur="recalculateAirconciergeTotal"
        />
      </VCol>

      <VCol
        v-if="isEdit"
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.accounting_adjustment"
          label="Account Adjustment (Expense)"
          placeholder="0.00"
          :error="fieldError('accounting_adjustment')"
          @update:model-value="updateField('accounting_adjustment', $event)"
          @blur="recalculateAirconciergeTotal"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        md="3"
      >
        <BaseInput
          :model-value="form.total_airconcierge_paid_out"
          label="TOTAL (What Air Concierge Paid Out)"
          placeholder="0.00"
          :hint="isEdit ? 'Includes safely insurance fee' : undefined"
          persistent-hint
          :error="fieldError('total_airconcierge_paid_out')"
          @update:model-value="updateField('total_airconcierge_paid_out', $event)"
        />
      </VCol>

      <template v-if="isEdit">
        <VCol cols="12">
          <VDivider class="my-2" />
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <div class="text-body-2 mb-1">
            Safely Guest Charge
          </div>
          <div class="booking-readonly-box text-body-2">
            ${{ formatMoney(form.safely_guest_rate) }}
            × {{ nights || 0 }}
            <small>(# of nights)</small>
            = ${{ safelyGuestCharge }}
          </div>
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <div class="text-body-2 mb-1">
            Safely Cost
          </div>
          <div class="booking-readonly-box text-body-2">
            ${{ formatMoney(form.safely_cost_rate) }}
            × {{ nights || 0 }}
            <small>(# of nights)</small>
            = ${{ safelyCost }}
          </div>
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <div class="text-body-2 mb-1">
            Air Concierge Safely Revenue
          </div>
          <div class="booking-readonly-box text-body-2">
            ${{ formatMoney(form.safely_revenue_rate) }}
            × {{ nights || 0 }}
            <small>(# of nights)</small>
            = ${{ safelyRevenue }}
          </div>
        </VCol>

        <VCol cols="12">
          <VDivider class="my-2" />
        </VCol>

        <VCol
          cols="12"
          sm="6"
          md="3"
        >
          <BaseInput
            :model-value="form.convenience_fee"
            label="Convenience Fee"
            placeholder="0.00"
            :error="fieldError('convenience_fee')"
            @update:model-value="updateField('convenience_fee', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          sm="6"
          md="3"
        >
          <BaseInput
            :model-value="form.staff_commession"
            label="Staff Bonus"
            placeholder="0.00"
            :error="fieldError('staff_commession')"
            @update:model-value="updateField('staff_commession', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          sm="6"
          md="3"
        >
          <BaseInput
            :model-value="form.commession_offset"
            label="Staff Bonus Offset"
            placeholder="0.00"
            :error="fieldError('commession_offset')"
            @update:model-value="updateField('commession_offset', $event)"
          />
        </VCol>

        <VCol cols="12">
          <VDivider class="my-2" />
        </VCol>

        <VCol
          cols="12"
          md="6"
        >
          <BaseInput
            :model-value="form.owner_payout_amount_from_airconcierge"
            label="Gross Booking Value from Air Concierge"
            placeholder="0.00"
            :error="fieldError('owner_payout_amount_from_airconcierge')"
            @update:model-value="updateField('owner_payout_amount_from_airconcierge', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          md="6"
        >
          <BaseInput
            :model-value="form.net_owner_payout"
            label="Net Owner Payout"
            placeholder="0.00"
            readonly
            :error="fieldError('net_owner_payout')"
          />
        </VCol>
      </template>

      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <BaseTextarea
          :model-value="form.owner_notes"
          label="Owner Notes"
          rows="3"
          :error="fieldError('owner_notes')"
          @update:model-value="updateField('owner_notes', $event)"
        />
      </VCol>

      <VCol
        v-if="isEdit"
        cols="12"
        md="4"
      >
        <BaseCheckbox
          :model-value="form.disable_autocalculation"
          label="Disable Autocalculation"
          @update:model-value="updateField('disable_autocalculation', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex flex-wrap justify-end gap-2 mt-2"
      >
        <BaseButton
          type="button"
          label="Cancel"
          @click="$emit('cancel')"
        />
        <BaseButton
          type="submit"
          :label="submitLabel"
          :loading="loading"
        />
      </VCol>
    </VRow>

    <template v-if="isEdit">
      <VAlert
        type="warning"
        variant="tonal"
        class="mt-6"
        density="comfortable"
      >
        Please note that when creating a new booking or editing an existing booking including changing reservation dates, the management fee and staff commission fee will not automatically update. Custom rules regarding management fee adjustments are not checked and must be revised manually.
      </VAlert>

      <VCard class="mt-6">
        <VCardItem>
          <VCardTitle>Booking Payments (Air Concierge Payouts)</VCardTitle>
          <template #append>
            <BaseButton
              size="small"
              variant="tonal"
              label="Add New Payment"
              disabled
            />
          </template>
        </VCardItem>
        <VCardText>
          <VTable class="text-no-wrap">
            <thead>
              <tr>
                <th class="text-center">
                  #
                </th>
                <th>Property</th>
                <th>Expense/Charge</th>
                <th class="text-center">
                  Purchase / Expense Incurred Date
                </th>
                <th class="text-center">
                  Payment Date
                </th>
                <th class="text-center">
                  Status
                </th>
                <th class="text-right">
                  Price
                </th>
                <th class="text-right">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="payment in payments"
                :key="payment.id"
              >
                <td class="text-center">
                  {{ payment.id }}
                </td>
                <td>{{ payment.property_title }}</td>
                <td>{{ payment.payment_type }}</td>
                <td class="text-center">
                  {{ payment.expense_incurred_date || '—' }}
                </td>
                <td class="text-center">
                  {{ payment.payment_date || '—' }}
                </td>
                <td class="text-center">
                  {{ payment.payment_status || '—' }}
                </td>
                <td class="text-right">
                  ${{ formatMoney(payment.price) }}
                </td>
                <td class="text-right text-medium-emphasis">
                  —
                </td>
              </tr>
              <tr v-if="!payments.length">
                <td
                  colspan="8"
                  class="text-center text-medium-emphasis py-6"
                >
                  No payments recorded for this booking yet.
                </td>
              </tr>
            </tbody>
            <tfoot v-if="payments.length">
              <tr>
                <th
                  colspan="6"
                  class="text-left"
                >
                  Total
                </th>
                <th class="text-right">
                  {{ paymentsTotalLabel }}
                </th>
                <th />
              </tr>
            </tfoot>
          </VTable>
        </VCardText>
      </VCard>
    </template>
  </VForm>
</template>

<style scoped>
.booking-readonly-box {
  background: rgba(var(--v-theme-on-surface), 0.04);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 6px;
  padding: 10px 12px;
  min-height: 40px;
}
</style>
