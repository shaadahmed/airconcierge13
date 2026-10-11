/**
 * Owner calendar block form helpers (legacy create-calendar-block + blocks-list edit modal).
 */

export const CLEANING_POST_STAY_OPTIONS = [
  { title: 'Select', value: '' },
  { title: 'Yes', value: '1' },
  { title: 'No', value: '0' },
]

export const CLEANING_POST_STAY_HINT
  = 'We cannot guarantee a cleaning can be arranged but we will use our best efforts and inform you if we cannot. As a redundancy, you should have a cleaning team on back up, with home access ability. CRITICAL: Upcoming reservations cannot be disturbed, delayed and most importantly, under no circumstances, can the home be accessed while guests are in residence (except for emergency purposes).'

export const CHARGE_AGREEMENT_LABEL
  = 'Air Concierge will use its best efforts to block these dates on all calendars for your listing upon receipt of this form the same day if received by 12pm PST, but could be the next one to two business days. As such please know that should either a) any existing reservations conflict with these dates, those must be honored unless we\'ve made other arrangements in writing and b) in the unlikely but possible situation should these dates that you request to be blocked also be booked by a traveler before Air Concierge has had the ability to block all calendars on your behalf, then the travelers reservation will take precedence and cannot be cancelled by us.'

export const TERMS_AGREEMENT_LABEL
  = 'I agree to be bound by the terms of the Air Concierge Inc. privacy policy and all terms covered in the terms of use and/or those terms that were agreed upon in the Management Agreement and/or those other agreements between me and Air Concierge.'

export const CLEANING_AGREEMENT_LABEL
  = 'Owner Block Cleanings will be scheduled promptly at 11am on the day of an Owner Block End Date. If our cleaners are able to accommodate the cleaning, then please have the home vacated by this time. If the cleaning staff arrives and the home is occupied, they will not wait and will not perform the cleaning which may have other consequences. A trip charge of $100 will be charged to the account. We apologize for the formality of this provision but our staff and vendors have tight windows and availability.'

export const UPDATE_TERMS_LABEL
  = 'I have read and agreed to the terms in full listed at http://www.airconcierge.net/terms-of-use/'

export const LATE_CANCELLATION_LABEL
  = 'By checking this box I agree that this request is considered last minute and may incur a $100 cancellation of cleaning service of the original date in which the cleaning was arranged.'

export const emptyOwnerBlockForm = (mode = 'create') => {
  const base = {
    property_id: null,
    reservation_start_date: '',
    reservation_end_date: '',
    cleaning_post_order_stay: '',
    booking_notes: '',
    updation_confirmation: false,
    late_cancellation_confirmation: false,
  }

  if (mode === 'edit')
    return base

  return {
    ...base,
    first_name: '',
    last_name: '',
    email: '',
    charge_agreement: false,
    terms_agreement: false,
    cleaning_agreement: false,
  }
}

export const ownerBlockFormFromRecord = record => {
  if (!record)
    return emptyOwnerBlockForm('edit')

  const exitCleaning = record.exit_cleaning ?? record.cleaning_post_order_stay

  return {
    ...emptyOwnerBlockForm('edit'),
    property_id: record.property_id ?? record.property?.id ?? null,
    reservation_start_date: record.reservation_start_date || record.start_date || '',
    reservation_end_date: record.reservation_end_date || record.end_date || '',
    cleaning_post_order_stay: exitCleaning === true || exitCleaning === 1 || exitCleaning === '1'
      ? '1'
      : (exitCleaning === false || exitCleaning === 0 || exitCleaning === '0' ? '0' : ''),
    booking_notes: record.booking_notes || record.notes || '',
  }
}

export const nightsBetween = (start, end) => {
  if (!start || !end)
    return null

  const startDate = new Date(start)
  const endDate = new Date(end)
  if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()))
    return null

  const nights = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24))

  return nights > 0 ? nights : null
}

export const formatMoney = value => {
  const amount = Number(value)
  if (!Number.isFinite(amount))
    return '—'

  return amount.toLocaleString(undefined, {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}
