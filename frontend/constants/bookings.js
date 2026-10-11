/**
 * Booking form options and helpers (legacy create/edit field parity).
 */

const MONTH_NAMES = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

const currentYear = () => new Date().getFullYear()
const currentMonth = () => new Date().getMonth() + 1

const todayIso = () => {
  const now = new Date()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')

  return `${now.getFullYear()}-${month}-${day}`
}

export const BOOKING_MONTH_OPTIONS = MONTH_NAMES.map((name, index) => {
  const value = String(index + 1).padStart(2, '0')

  return { title: `${value} ${name}`, value }
})

export const BOOKING_YEAR_OPTIONS = Array.from(
  { length: currentYear() - 2014 + 3 },
  (_, index) => {
    const year = 2014 + index

    return { title: String(year), value: String(year) }
  },
).reverse()

/** Create form uses a select; labels match legacy create.blade.php */
export const BOOKING_TOT_MODE_CREATE_OPTIONS = [
  { title: 'Paid by Air Concierge to City', value: 'paid_by_airconcierge_to_city' },
  { title: "Paid at Owner's election", value: 'paid_at_owners_election' },
  { title: 'Charged to guest / Paid to city by Booking Platform', value: 'charged_to_guest_paid_to_city_by_airbnb' },
  { title: 'No TOT (Reservation meets long term stay rule)', value: 'none' },
]

/** Edit form radios; labels match legacy edit.blade.php */
export const BOOKING_TOT_MODE_EDIT_OPTIONS = [
  { title: 'Elect to remit', value: 'paid_at_owners_election' },
  { title: 'TOT Paid by AC to city', value: 'paid_by_airconcierge_to_city' },
  { title: 'No TOT (Reservation meets long term stay rule)', value: 'none' },
  { title: 'Charged to guest/ Paid to city by Booking Platform', value: 'charged_to_guest_paid_to_city_by_airbnb' },
]

export const BOOKING_PLATFORM_OPTIONS = [
  { title: 'Airbnb', value: 1 },
  { title: 'VRBO', value: 2 },
  { title: 'Booking.com', value: 3 },
  { title: 'Direct', value: 4 },
  { title: 'Other', value: 16 },
]

export const BOOKING_CANCELLED_OPTIONS = [
  { title: 'Cancelled with no Payout', value: '1' },
  { title: 'Cancelled with Full Payout (financials are being included in fees and Payouts)', value: '3' },
  { title: 'Cancelled with Partial Payout (financials are being included in fees and Payouts)', value: '4' },
  { title: 'Cancelled with Full or Partial Payout (financials are being included in fees and Payouts)', value: '2' },
  { title: 'No', value: '0' },
]

export const BOOKING_DISPUTE_STATUS_OPTIONS = [
  { title: 'No Dispute', value: '1' },
  { title: 'Flag as Dispute', value: '0' },
  { title: 'Dispute Resolved', value: '2' },
]

/** UI preview guests until guest search API is wired. */
export const BOOKING_GUEST_OPTIONS = [
  { title: 'Alex Morgan', value: 501, phone: '310-555-0101', email: 'alex.morgan@example.com' },
  { title: 'Jordan Lee', value: 502, phone: '858-555-0144', email: 'jordan.lee@example.com' },
  { title: 'Sam Rivera', value: 503, phone: '305-555-0199', email: 'sam.rivera@example.com' },
  { title: 'Casey Nguyen', value: 504, phone: '415-555-0177', email: 'casey.nguyen@example.com' },
  { title: 'Taylor Brooks', value: 505, phone: '530-555-0122', email: 'taylor.brooks@example.com' },
  { title: 'Riley Quinn', value: 506, phone: '707-555-0188', email: 'riley.quinn@example.com' },
  { title: 'Morgan Ellis', value: 507, phone: '775-555-0133', email: 'morgan.ellis@example.com' },
  { title: 'Jamie Patel', value: 508, phone: '530-555-0166', email: 'jamie.patel@example.com' },
]

/** UI preview staff list (legacy regional managers). */
export const BOOKING_STAFF_OPTIONS = [
  { title: 'Select', value: null },
  { title: 'Pat Rivera', value: 11 },
  { title: 'Chris Nguyen', value: 12 },
  { title: 'Sam Ortiz', value: 13 },
]

export const GUEST_PAID_FIELDS = [
  'accomodations',
  'cleaning_fee',
  'tot_charged_to_guest',
  'daily_utility_fee',
  'security_deposit',
  'pet_fee',
  'airbnb_cohost_payout',
  'accounting_adjustment_income',
  'safely_insurance_fee',
]

export const AIRCONCIERGE_PAID_FIELDS = [
  'site_listing_fee',
  'credit_card_transaction_fee',
  'management_fee',
  'payment_of_cleaners',
  'accounting_adjustment',
  'total_paid_by_airconcierge_to_city',
  'safely_insurance_fee',
]

export function moneyNumber(value) {
  if (value === '' || value == null)
    return 0

  const parsed = Number(String(value).replace(/,/g, ''))

  return Number.isFinite(parsed) ? parsed : 0
}

export function formatMoney(value) {
  return moneyNumber(value).toFixed(2)
}

export function nightsBetween(start, end) {
  if (!start || !end)
    return 0

  const startDate = new Date(`${start}T00:00:00`)
  const endDate = new Date(`${end}T00:00:00`)
  if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()))
    return 0

  const diff = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24))

  return diff > 0 ? diff : 0
}

export function emptyBookingForm(mode = 'create') {
  const month = String(currentMonth()).padStart(2, '0')
  const year = String(currentYear())
  const today = todayIso()

  return {
    property_id: null,
    ac_management_fee: '0',
    is_cohost_booking: '0',
    month,
    year,
    reservation_start_date: mode === 'create' ? today : '',
    reservation_end_date: '',
    no_of_nights: '',
    booking_date: mode === 'create' ? today : '',
    guest_ids: [],
    guest_id: null,
    no_of_guests: '',
    platform_id: null,
    tot_mode: '',
    cancelled_booking: '',
    is_payment_clear: '',
    booking_code: '',
    parent_id: '',
    guest_name: '',
    phone: '',
    email: '',
    accomodations: '',
    cleaning_fee: '',
    tot_charged_to_guest: '',
    daily_utility_fee: '',
    security_deposit: '',
    pet_fee: '',
    airbnb_cohost_payout: '',
    accounting_adjustment_income: '',
    safely_insurance_fee: '',
    total_guest_paid: '',
    site_listing_fee: '',
    credit_card_transaction_fee: '',
    management_fee: '',
    payment_of_cleaners: '',
    air_concierge_staff: null,
    accounting_adjustment: '',
    convenience_fee: '',
    staff_commession: '',
    commession_offset: '',
    total_elect_to_remit: '',
    total_paid_by_airconcierge_to_city: '',
    total_airconcierge_paid_out: '',
    owner_payout_amount_from_airconcierge: '',
    net_owner_payout: '',
    owner_notes: '',
    disable_autocalculation: false,
    safely_guest_rate: 5,
    safely_cost_rate: 2,
    safely_revenue_rate: 3,
  }
}

export function bookingFormFromRecord(booking) {
  const base = emptyBookingForm('edit')
  if (!booking)
    return base

  const money = key => {
    if (booking[key] == null || booking[key] === '')
      return ''

    return formatMoney(booking[key])
  }

  const platformMatch = BOOKING_PLATFORM_OPTIONS.find(
    option => option.title === booking.platform || String(option.value) === String(booking.platform_id),
  )

  const guestMatch = BOOKING_GUEST_OPTIONS.find(
    option => option.title === booking.guest_name || String(option.value) === String(booking.guest_id),
  )

  return {
    ...base,
    property_id: booking.property_id ?? booking.property?.id ?? null,
    ac_management_fee: booking.ac_management_fee != null ? String(booking.ac_management_fee) : base.ac_management_fee,
    is_cohost_booking: booking.is_cohost_booking != null ? String(booking.is_cohost_booking) : '0',
    month: booking.month != null ? String(booking.month).padStart(2, '0') : base.month,
    year: booking.year != null ? String(booking.year) : base.year,
    reservation_start_date: booking.reservation_start_date || '',
    reservation_end_date: booking.reservation_end_date || '',
    no_of_nights: booking.no_of_nights != null ? String(booking.no_of_nights) : '',
    booking_date: booking.booking_date || '',
    guest_id: booking.guest_id ?? guestMatch?.value ?? null,
    guest_ids: booking.guest_id ? [booking.guest_id] : (guestMatch ? [guestMatch.value] : []),
    no_of_guests: booking.no_of_guests != null ? String(booking.no_of_guests) : '',
    platform_id: booking.platform_id ?? platformMatch?.value ?? null,
    tot_mode: booking.tot_mode || '',
    cancelled_booking: booking.cancelled_booking === true
      ? '1'
      : (booking.cancelled_booking != null && booking.cancelled_booking !== false
        ? String(booking.cancelled_booking)
        : '0'),
    is_payment_clear: booking.is_payment_clear != null ? String(booking.is_payment_clear) : '',
    booking_code: booking.booking_code || '',
    parent_id: booking.parent_id != null ? String(booking.parent_id) : '',
    guest_name: booking.guest_name || guestMatch?.title || '',
    phone: booking.phone || booking.guest_phone || guestMatch?.phone || '',
    email: booking.email || booking.guest_email || guestMatch?.email || '',
    accomodations: money('accomodations'),
    cleaning_fee: money('cleaning_fee'),
    tot_charged_to_guest: money('tot_charged_to_guest'),
    daily_utility_fee: money('daily_utility_fee'),
    security_deposit: money('security_deposit'),
    pet_fee: money('pet_fee'),
    airbnb_cohost_payout: money('airbnb_cohost_payout'),
    accounting_adjustment_income: money('accounting_adjustment_income'),
    safely_insurance_fee: money('safely_insurance_fee'),
    total_guest_paid: money('total_guest_paid'),
    site_listing_fee: money('site_listing_fee'),
    credit_card_transaction_fee: money('credit_card_transaction_fee'),
    management_fee: money('management_fee'),
    payment_of_cleaners: money('payment_of_cleaners'),
    air_concierge_staff: booking.air_concierge_staff ?? null,
    accounting_adjustment: money('accounting_adjustment'),
    convenience_fee: money('convenience_fee'),
    staff_commession: money('staff_commession'),
    commession_offset: money('commession_offset'),
    total_elect_to_remit: money('total_elect_to_remit'),
    total_paid_by_airconcierge_to_city: money('total_paid_by_airconcierge_to_city'),
    total_airconcierge_paid_out: money('total_airconcierge_paid_out'),
    owner_payout_amount_from_airconcierge: money('owner_payout_amount_from_airconcierge') || money('gross_owner_payout'),
    net_owner_payout: money('net_owner_payout') || money('owner_payout_amount_from_airconcierge'),
    owner_notes: booking.owner_notes || '',
    safely_guest_rate: booking.safely_guest_rate ?? 5,
    safely_cost_rate: booking.safely_cost_rate ?? 2,
    safely_revenue_rate: booking.safely_revenue_rate ?? 3,
  }
}
