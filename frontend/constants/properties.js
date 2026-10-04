/** Option lists mirrored from legacy property create/edit screens. */

export const PROPERTY_STATUS_OPTIONS = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
  { title: 'Snoozed', value: 2 },
]

export const PROPERTY_STATUS_CREATE_OPTIONS = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
]

export const YES_NO_OPTIONS = [
  { title: 'Yes', value: 1 },
  { title: 'No', value: 0 },
]

export const AIRBNB_TOT_MODE_OPTIONS = [
  { title: 'Paid by Air Concierge to City', value: 'paid_by_airconcierge_to_city' },
  { title: "Paid at Owner's election", value: 'paid_at_owners_election' },
  { title: 'Charged to guest / Paid to city by Booking Platform', value: 'charged_to_guest_paid_to_city_by_airbnb' },
  { title: 'Not Charged/Calculated (Min Stay Is Not "Short Term")', value: 'none' },
]

/** Legacy stores VRBO mode as integer 1–4. */
export const VRBO_TOT_MODE_OPTIONS = [
  { title: 'Paid by Air Concierge to City', value: 1 },
  { title: "Paid at Owner's election", value: 2 },
  { title: 'Charged to guest / Paid to city by Booking Platform', value: 3 },
  { title: 'Not Charged/Calculated (Min Stay Is Not "Short Term")', value: 4 },
]

/** Airbnb string mode ↔ VRBO integer mode (legacy parity). */
export const AIRBNB_TO_VRBO_TOT_MODE = {
  paid_by_airconcierge_to_city: 1,
  paid_at_owners_election: 2,
  charged_to_guest_paid_to_city_by_airbnb: 3,
  none: 4,
}

export const VRBO_TO_AIRBNB_TOT_MODE = Object.fromEntries(
  Object.entries(AIRBNB_TO_VRBO_TOT_MODE).map(([airbnb, vrbo]) => [String(vrbo), airbnb]),
)

export const AIRBNB_TOT_MODE_REQUIRES_METHOD = 'paid_by_airconcierge_to_city'
export const VRBO_TOT_MODE_REQUIRES_METHOD = 1

export const airbnbTotModeRequiresMethod = mode => mode === AIRBNB_TOT_MODE_REQUIRES_METHOD
export const vrboTotModeRequiresMethod = mode => Number(mode) === VRBO_TOT_MODE_REQUIRES_METHOD

export const PAYMENT_METHOD_OPTIONS = [
  { title: 'Direct Deposit', value: 'Direct Deposit' },
  { title: 'Co Host - Direct Deposit', value: 'Direct Deposit (Co Host)' },
  { title: 'Co Host - Credit Card', value: 'Credit Card (Co Host)' },
  { title: 'Airbnb Co Host', value: 'Airbnb Co Host' },
  { title: 'PayPal', value: 'PayPal' },
]

export const CONTRACT_END_REASON_OPTIONS = [
  'AC fee too high (Owner took over)',
  'Air Concierge terminated owner',
  'Air Concierge terminated owner (property bad fit)',
  'Air Concierge terminated owner (owner bad fit)',
  'City/law restriction',
  'HOA restriction',
  'House Sold (listed when STR started)',
  'House Sold (listed after starting STR)',
  'Neighbor issues (friction)',
  'Owner displeased with costs to operate (maintenance, supplies, eg)',
  'Owner not meeting STR financial goals',
  'Owner switching to long term rental',
  'Owner took back for personal use purposes',
  'Owner took back due to neighbor STR complaints',
  'Owner terminated Air Concierge (performance purposes)',
  'Owner terminated AC to self-manage',
  'Owner terminated AC hired another manager',
  'Signed up but never went live (property did not become rental)',
  'Signed up but never went live (property sold)',
  'Unknown',
].map(reason => ({ title: reason, value: reason }))

/** Placeholder TOT reporting methods until backend lookup is wired. */
export const TOT_METHOD_OPTIONS = [
  { title: 'Airbnb Collects and Remits', value: 'Airbnb Collects and Remits' },
  { title: 'Host Collects and Remits', value: 'Host Collects and Remits' },
  { title: 'Manager Collects and Remits', value: 'Manager Collects and Remits' },
  { title: 'Not Applicable', value: 'Not Applicable' },
]

export const US_STATE_OPTIONS = [
  'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
  'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
  'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
  'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
  'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
  'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
  'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia',
  'Wisconsin', 'Wyoming', 'District of Columbia',
].map(state => ({ title: state, value: state }))

export const MANAGEMENT_TYPE_OPTIONS = [
  { title: 'Full Service', value: 1 },
  { title: 'Co-Host', value: 2 },
  { title: 'Consulting', value: 3 },
]

export const ADDITIONAL_FEE_TYPE_OPTIONS = [
  { title: 'Community fee', value: 'community_fee' },
  { title: 'Resort fee', value: 'resort_fee' },
  { title: 'Daily use fee', value: 'daily_use_fee' },
  { title: 'Other', value: 'other' },
]

export const ADDITIONAL_FEE_CURRENCY_OPTIONS = [
  { title: 'Dollar', value: 'doller' },
  { title: 'Percentage', value: 'percentage' },
]

export const ADDITIONAL_FEE_APPLICATION_OPTIONS = [
  { title: 'Daily', value: 'daily' },
  { title: 'Per reservation', value: 'per_reservation' },
]

export const LISTING_PLATFORM_OPTIONS = [
  { title: 'Airbnb', value: 'Airbnb' },
  { title: 'VRBO', value: 'VRBO' },
  { title: 'Booking.com', value: 'Booking.com' },
  { title: 'Other', value: 'Other' },
]

export const LISTING_STATUS_OPTIONS = [
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 },
]

export function propertyStatusLabel(status) {
  if (status === true || status === 1 || status === '1')
    return 'Active'
  if (status === 2 || status === '2')
    return 'Snoozed'
  if (status === false || status === 0 || status === '0')
    return 'Inactive'

  return '—'
}

export function propertyStatusColor(status) {
  if (status === true || status === 1 || status === '1')
    return 'success'
  if (status === 2 || status === '2')
    return 'warning'

  return 'secondary'
}

export function labelFromOptions(options, value) {
  if (value === null || value === undefined || value === '')
    return '—'

  const match = options.find(option => String(option.value) === String(value))

  return match?.title || String(value)
}

export function yesNoLabel(value) {
  if (value === true || value === 1 || value === '1')
    return 'Yes'
  if (value === false || value === 0 || value === '0')
    return 'No'

  return '—'
}
