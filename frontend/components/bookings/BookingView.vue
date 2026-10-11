<script setup>
import {
  BOOKING_CANCELLED_OPTIONS,
  BOOKING_DISPUTE_STATUS_OPTIONS,
  BOOKING_MONTH_OPTIONS,
  BOOKING_PLATFORM_OPTIONS,
  BOOKING_STAFF_OPTIONS,
  BOOKING_TOT_MODE_CREATE_OPTIONS,
  formatMoney,
  moneyNumber,
} from '@/constants/bookings'
import {
  bookingAcRevenueLabel,
  bookingGuestPaidLabel,
  bookingLocation,
  bookingNightlyRateLabel,
  bookingPlatformLabel,
  bookingPropertyTitle,
  bookingStatusColor,
  bookingStatusLabel,
  isClearedBooking,
} from '@/mocks/bookings'

const props = defineProps({
  booking: { type: Object, required: true },
})

const labelFromOptions = (options, value) => {
  if (value == null || value === '')
    return '—'

  const match = options.find(option => String(option.value) === String(value))

  return match?.title || String(value)
}

const displayText = value => {
  if (value == null || value === '')
    return '—'

  return String(value)
}

const money = value => {
  if (value == null || value === '')
    return '—'

  return `$${formatMoney(value)}`
}

const formatDate = value => {
  if (!value)
    return '—'

  const date = new Date(`${value}T00:00:00`)
  if (Number.isNaN(date.getTime()))
    return String(value)

  return date.toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const yesNo = value => {
  if (value === true || value === 1 || value === '1')
    return 'Yes'
  if (value === false || value === 0 || value === '0')
    return 'No'

  return '—'
}

const cancelledOptionValue = value => {
  if (value === true || value === 1 || value === '1')
    return '1'
  if (value === false || value === 0 || value === '0' || value == null || value === '')
    return '0'

  return value
}

const nights = computed(() => {
  if (props.booking.no_of_nights != null && props.booking.no_of_nights !== '')
    return Number(props.booking.no_of_nights)

  const start = props.booking.reservation_start_date
  const end = props.booking.reservation_end_date
  if (!start || !end)
    return null

  const startDate = new Date(`${start}T00:00:00`)
  const endDate = new Date(`${end}T00:00:00`)
  if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()))
    return null

  const diff = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24))

  return diff > 0 ? diff : null
})

const payments = computed(() => {
  const list = props.booking.payments

  return Array.isArray(list) ? list : []
})

const paymentsTotal = computed(() => {
  if (props.booking.payments_total != null && props.booking.payments_total !== '')
    return moneyNumber(props.booking.payments_total)

  return payments.value.reduce((sum, payment) => sum + moneyNumber(payment.price), 0)
})

const staffLabel = computed(() => {
  const staffId = props.booking.air_concierge_staff
  if (staffId == null || staffId === '')
    return '—'

  return labelFromOptions(BOOKING_STAFF_OPTIONS, staffId)
})

const summaryMetrics = computed(() => [
  {
    label: 'Guest paid',
    value: bookingGuestPaidLabel(props.booking),
    icon: 'bx-wallet',
  },
  {
    label: 'AC revenue',
    value: bookingAcRevenueLabel(props.booking),
    icon: 'bx-trending-up',
  },
  {
    label: 'Owner payout',
    value: money(props.booking.net_owner_payout ?? props.booking.owner_payout_amount_from_airconcierge),
    icon: 'bx-home-circle',
  },
  {
    label: 'Nightly rate',
    value: bookingNightlyRateLabel(props.booking),
    icon: 'bx-moon',
  },
])

const sections = computed(() => {
  const booking = props.booking

  return [
    {
      title: 'Stay',
      items: [
        { label: 'Month', value: labelFromOptions(BOOKING_MONTH_OPTIONS, booking.month != null ? String(booking.month).padStart(2, '0') : '') },
        { label: 'Year', value: displayText(booking.year) },
        { label: 'Check-in', value: formatDate(booking.reservation_start_date) },
        { label: 'Check-out', value: formatDate(booking.reservation_end_date) },
        { label: 'Nights', value: nights.value != null ? String(nights.value) : '—' },
        { label: 'Booking date', value: formatDate(booking.booking_date) },
      ],
    },
    {
      title: 'Guest',
      items: [
        { label: 'Name', value: displayText(booking.guest_name) },
        { label: 'Email', value: displayText(booking.email || booking.guest_email) },
        { label: 'Phone', value: displayText(booking.phone) },
        { label: 'Guests', value: displayText(booking.no_of_guests) },
      ],
    },
    {
      title: 'Property & channel',
      items: [
        { label: 'Property', value: bookingPropertyTitle(booking) },
        { label: 'Location', value: bookingLocation(booking) },
        { label: 'Management fee %', value: booking.ac_management_fee != null && booking.ac_management_fee !== '' ? `${booking.ac_management_fee}%` : '—' },
        { label: 'Platform', value: bookingPlatformLabel(booking) || labelFromOptions(BOOKING_PLATFORM_OPTIONS, booking.platform_id) },
        { label: 'Booking code', value: displayText(booking.booking_code) },
        { label: 'Parent booking', value: booking.parent_id ? String(booking.parent_id) : 'Individual booking' },
        { label: 'Co-host booking', value: yesNo(booking.is_cohost_booking) },
      ],
    },
    {
      title: 'Status & tax',
      items: [
        { label: 'Status', value: bookingStatusLabel(booking), chip: bookingStatusColor(booking) },
        { label: 'Cancelled', value: labelFromOptions(BOOKING_CANCELLED_OPTIONS, cancelledOptionValue(booking.cancelled_booking)) },
        { label: 'Payment cleared', value: isClearedBooking(booking) ? 'Yes' : yesNo(booking.is_payment_clear) },
        { label: 'Dispute status', value: labelFromOptions(BOOKING_DISPUTE_STATUS_OPTIONS, booking.dispute_status ?? '1') },
        { label: 'TOT mode', value: labelFromOptions(BOOKING_TOT_MODE_CREATE_OPTIONS, booking.tot_mode) },
      ],
    },
    {
      title: 'What guest paid',
      accent: true,
      items: [
        { label: 'Accommodations', value: money(booking.accomodations) },
        { label: 'Cleaning fee', value: money(booking.cleaning_fee) },
        { label: 'TOT charged to guest', value: money(booking.tot_charged_to_guest) },
        { label: 'Resort fee', value: money(booking.daily_utility_fee) },
        { label: 'Security deposit', value: money(booking.security_deposit) },
        { label: 'Pet fee', value: money(booking.pet_fee) },
        { label: 'Damage claim (income)', value: money(booking.airbnb_cohost_payout) },
        { label: 'Account adjustment (income)', value: money(booking.accounting_adjustment_income) },
        { label: 'Safely insurance fee', value: money(booking.safely_insurance_fee) },
        { label: 'Total guest paid', value: money(booking.total_guest_paid), emphasize: true },
      ],
    },
    {
      title: 'What Air Concierge paid out',
      accent: true,
      items: [
        { label: 'Site listing fee', value: money(booking.site_listing_fee) },
        { label: 'Credit card processing', value: money(booking.credit_card_transaction_fee) },
        { label: 'Management fee', value: money(booking.management_fee) },
        { label: 'Paid to cleaners', value: money(booking.payment_of_cleaners) },
        { label: 'Air Concierge staff', value: staffLabel.value },
        { label: 'Account adjustment (expense)', value: money(booking.accounting_adjustment) },
        { label: 'Convenience fee', value: money(booking.convenience_fee) },
        { label: 'Staff bonus', value: money(booking.staff_commession) },
        { label: 'Staff bonus offset', value: money(booking.commession_offset) },
        { label: 'TOT (elect to remit)', value: money(booking.total_elect_to_remit) },
        { label: 'TOT (paid by AC to city)', value: money(booking.total_paid_by_airconcierge_to_city) },
        { label: 'Total AC paid out', value: money(booking.total_airconcierge_paid_out), emphasize: true },
        { label: 'Gross booking value from AC', value: money(booking.owner_payout_amount_from_airconcierge) },
        { label: 'Net owner payout', value: money(booking.net_owner_payout), emphasize: true },
      ],
    },
  ]
})
</script>

<template>
  <div class="booking-view">
    <VRow class="mb-4">
      <VCol
        cols="12"
        md="4"
      >
        <VImg
          v-if="booking.property?.property_image_url"
          :src="booking.property.property_image_url"
          height="220"
          cover
          class="rounded-lg"
        />
        <div
          v-else
          class="booking-view__placeholder rounded-lg d-flex align-center justify-center"
        >
          <VIcon
            icon="bx-calendar-check"
            size="48"
            class="text-medium-emphasis"
          />
        </div>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <div class="d-flex align-center flex-wrap gap-2 mb-2">
          <h2 class="text-h5 mb-0">
            {{ booking.booking_code || `Booking #${booking.id}` }}
          </h2>
          <VChip
            size="small"
            :color="bookingStatusColor(booking)"
            label
          >
            {{ bookingStatusLabel(booking) }}
          </VChip>
          <VChip
            size="small"
            variant="tonal"
            label
          >
            {{ bookingPlatformLabel(booking) }}
          </VChip>
        </div>

        <p class="text-body-1 font-weight-medium mb-1">
          {{ bookingPropertyTitle(booking) }}
        </p>
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ bookingLocation(booking) }}
        </p>

        <div class="d-flex flex-wrap gap-4 text-body-2 mb-4">
          <div class="d-flex align-center gap-2">
            <VIcon
              icon="bx-calendar"
              size="18"
              class="text-medium-emphasis"
            />
            <span>
              {{ formatDate(booking.reservation_start_date) }}
              →
              {{ formatDate(booking.reservation_end_date) }}
            </span>
          </div>
          <div class="d-flex align-center gap-2">
            <VIcon
              icon="bx-moon"
              size="18"
              class="text-medium-emphasis"
            />
            <span>{{ nights != null ? `${nights} nights` : '—' }}</span>
          </div>
          <div class="d-flex align-center gap-2">
            <VIcon
              icon="bx-user"
              size="18"
              class="text-medium-emphasis"
            />
            <span>{{ booking.guest_name || '—' }}</span>
          </div>
          <div class="d-flex align-center gap-2">
            <VIcon
              icon="bx-group"
              size="18"
              class="text-medium-emphasis"
            />
            <span>{{ booking.no_of_guests != null && booking.no_of_guests !== '' ? `${booking.no_of_guests} guests` : '—' }}</span>
          </div>
        </div>

        <div class="booking-view__metrics">
          <div
            v-for="metric in summaryMetrics"
            :key="metric.label"
            class="booking-view__metric"
          >
            <div class="d-flex align-center gap-2 mb-1">
              <VIcon
                :icon="metric.icon"
                size="16"
                class="text-medium-emphasis"
              />
              <span class="text-caption text-medium-emphasis">
                {{ metric.label }}
              </span>
            </div>
            <div class="text-subtitle-1 font-weight-medium">
              {{ metric.value }}
            </div>
          </div>
        </div>
      </VCol>
    </VRow>

    <div class="booking-view__sections">
      <VCard
        v-for="section in sections"
        :key="section.title"
        variant="outlined"
        class="booking-view__section"
        :class="{ 'booking-view__section--accent': section.accent }"
      >
        <VCardItem>
          <VCardTitle class="text-subtitle-1">
            {{ section.title }}
          </VCardTitle>
        </VCardItem>
        <VCardText>
          <div
            v-for="item in section.items"
            :key="item.label"
            class="booking-view__row"
            :class="{ 'booking-view__row--emphasize': item.emphasize }"
          >
            <div class="text-caption text-medium-emphasis">
              {{ item.label }}
            </div>
            <div
              class="text-body-2"
              :class="{ 'font-weight-medium': item.emphasize }"
            >
              <VChip
                v-if="item.chip"
                size="x-small"
                :color="item.chip"
                label
              >
                {{ item.value }}
              </VChip>
              <span v-else>{{ item.value }}</span>
            </div>
          </div>
        </VCardText>
      </VCard>
    </div>

    <VCard
      variant="outlined"
      class="mt-2"
    >
      <VCardItem>
        <VCardTitle class="text-subtitle-1">
          Booking payments
        </VCardTitle>
        <template
          v-if="payments.length"
          #append
        >
          <span class="text-body-2 text-medium-emphasis">
            Total ${{ formatMoney(paymentsTotal) }}
          </span>
        </template>
      </VCardItem>
      <VCardText>
        <VTable
          v-if="payments.length"
          class="text-no-wrap"
        >
          <thead>
            <tr>
              <th>Expense / charge</th>
              <th>Status</th>
              <th>Incurred</th>
              <th>Paid</th>
              <th class="text-right">
                Amount
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="payment in payments"
              :key="payment.id"
            >
              <td>{{ payment.payment_type || '—' }}</td>
              <td>{{ payment.payment_status || '—' }}</td>
              <td>{{ payment.expense_incurred_date || '—' }}</td>
              <td>{{ payment.payment_date || '—' }}</td>
              <td class="text-right">
                ${{ formatMoney(payment.price) }}
              </td>
            </tr>
          </tbody>
        </VTable>
        <p
          v-else
          class="text-body-2 text-medium-emphasis mb-0"
        >
          No payments recorded for this booking yet.
        </p>
      </VCardText>
    </VCard>

    <VRow class="mt-2">
      <VCol
        cols="12"
        md="6"
      >
        <VCard variant="outlined">
          <VCardItem>
            <VCardTitle class="text-subtitle-1">
              Owner notes
            </VCardTitle>
          </VCardItem>
          <VCardText>
            <p class="text-body-2 mb-0 whitespace-pre-wrap">
              {{ booking.owner_notes || '—' }}
            </p>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="6"
      >
        <VCard variant="outlined">
          <VCardItem>
            <VCardTitle class="text-subtitle-1">
              Booking notes
            </VCardTitle>
          </VCardItem>
          <VCardText>
            <p class="text-body-2 mb-0 whitespace-pre-wrap">
              {{ booking.booking_notes || '—' }}
            </p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.booking-view__placeholder {
  height: 220px;
  background: rgba(75, 70, 92, 0.06);
}

.booking-view__metrics {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

@media (min-width: 600px) {
  .booking-view__metrics {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

.booking-view__metric {
  padding: 12px 14px;
  border-radius: 10px;
  background: rgba(var(--v-theme-primary), 0.06);
  border: 1px solid rgba(var(--v-theme-primary), 0.12);
}

.booking-view__sections {
  column-count: 1;
  column-gap: 24px;
}

@media (min-width: 960px) {
  .booking-view__sections {
    column-count: 2;
  }
}

.booking-view__section {
  break-inside: avoid;
  margin-bottom: 24px;
  width: 100%;
}

.booking-view__section--accent {
  border-color: rgba(var(--v-theme-primary), 0.28);
}

.booking-view__row {
  display: grid;
  grid-template-columns: minmax(140px, 45%) 1fr;
  gap: 8px 16px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(75, 70, 92, 0.08);
}

.booking-view__row:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.booking-view__row--emphasize {
  margin-top: 4px;
  padding-top: 12px;
  border-top: 1px solid rgba(var(--v-theme-primary), 0.16);
}

.whitespace-pre-wrap {
  white-space: pre-wrap;
}
</style>
