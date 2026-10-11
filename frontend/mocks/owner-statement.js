/**
 * UI-preview fixtures for Owner Statements until the API is wired.
 */

import { MOCK_PROPERTIES } from '@/mocks/properties'

export const OWNER_STATEMENT_DATE_RANGES = [
  'last_month',
  'last_quarter',
  'last_year',
  'last_12_months',
  'this_month',
  'this_quarter',
  'this_year',
  'next_month',
  'next_quarter',
  'next_year',
  'specific_month',
]

export const OWNER_STATEMENT_SCHEMA_GAPS = [
  'daily_utility_fee',
  'security_deposit_deductions',
  'pet_fee',
  'airbnb_cohost_payout',
  'accounting_adjustment_income',
  'site_listing_fee',
  'credit_card_transaction_fee',
  'payment_of_cleaners',
  'concierge_restocking',
  'maintance',
  'total_paid_by_airconcierge_to_city',
  'accounting_adjustment',
  'total_elect_to_remit',
]

const reportByPropertyId = {
  101: {
    total_reservations: 8,
    booked_nights: 29,
    blocked_nights: 4,
    occupancy_percentage: 78,
    excluded_linked_bookings_count: 1,
    average_daily_rate: 312.5,
    accomodations: 9062.5,
    cleaning_fee: 1480,
    tot_charged_to_guest: 905.4,
    daily_utility_fee: 290,
    security_deposit_deductions: 125,
    pet_fee: 150,
    airbnb_cohost_payout: 0,
    accounting_adjustment_income: 40,
    safely_insurance_fee: 160,
    total_guest_paid: 12212.9,
    site_listing_fee: 1180.45,
    credit_card_transaction_fee: 96.2,
    payment_of_cleaners: 1480,
    booking_payments: 642.75,
    property_payments: 210,
    concierge_restocking: 185,
    management_fee: 1993.75,
    maintance: 275,
    total_paid_by_airconcierge_to_city: 905.4,
    accounting_adjustment: 0,
    owner_payout: 4820.15,
    convenience_fee: 35,
    management_markup: 48,
    total_income: 12212.9,
    total_expenses: 7228.55,
    net_income: 4984.35,
    total_elect_to_remit: 0,
    cohost_property_flag: 0,
    owner_notes: [
      {
        guest: 'Ava Morgan',
        start: '2026-03-04',
        end: '2026-03-08',
        comment: 'Owner requested extra firewood and late checkout coordination.',
        url: '/admin/bookings/1001',
      },
      {
        guest: 'Noah Patel',
        start: '2026-03-14',
        end: '2026-03-18',
        comment: 'Hot tub cover replaced mid-stay; charged under maintenance.',
        url: '/admin/bookings/1002',
      },
    ],
  },
  102: {
    total_reservations: 3,
    booked_nights: 11,
    blocked_nights: 7,
    occupancy_percentage: 42,
    excluded_linked_bookings_count: 0,
    average_daily_rate: 268,
    accomodations: 2948,
    cleaning_fee: 450,
    tot_charged_to_guest: 265.32,
    daily_utility_fee: 0,
    security_deposit_deductions: 0,
    pet_fee: 75,
    airbnb_cohost_payout: 200,
    accounting_adjustment_income: 0,
    safely_insurance_fee: 60,
    total_guest_paid: 3998.32,
    site_listing_fee: 412.4,
    credit_card_transaction_fee: 28.1,
    payment_of_cleaners: 450,
    booking_payments: 188.5,
    property_payments: 0,
    concierge_restocking: 65,
    management_fee: 589.6,
    maintance: 140,
    total_paid_by_airconcierge_to_city: 0,
    accounting_adjustment: 25,
    owner_payout: 1890,
    convenience_fee: 12,
    management_markup: 18,
    total_income: 3998.32,
    total_expenses: 1958.6,
    net_income: 2039.72,
    total_elect_to_remit: 265.32,
    cohost_property_flag: 1,
    owner_notes: [
      {
        guest: 'Riley Chen',
        start: '2026-02-20',
        end: '2026-02-24',
        comment: 'Owner block overlapped arrival day; housekeeping delayed by 2 hours.',
        url: '/admin/bookings/1003',
      },
    ],
  },
  103: {
    total_reservations: 12,
    booked_nights: 41,
    blocked_nights: 2,
    occupancy_percentage: 86,
    excluded_linked_bookings_count: 2,
    average_daily_rate: 418.75,
    accomodations: 17168.75,
    cleaning_fee: 2880,
    tot_charged_to_guest: 1545.19,
    daily_utility_fee: 615,
    security_deposit_deductions: 350,
    pet_fee: 0,
    airbnb_cohost_payout: 480,
    accounting_adjustment_income: 95,
    safely_insurance_fee: 240,
    total_guest_paid: 23373.94,
    site_listing_fee: 2412.8,
    credit_card_transaction_fee: 155.6,
    payment_of_cleaners: 2880,
    booking_payments: 980.25,
    property_payments: 420,
    concierge_restocking: 310,
    management_fee: 4292.19,
    maintance: 640,
    total_paid_by_airconcierge_to_city: 1545.19,
    accounting_adjustment: 60,
    owner_payout: 8920.4,
    convenience_fee: 55,
    management_markup: 72,
    total_income: 23373.94,
    total_expenses: 14456.03,
    net_income: 8917.91,
    total_elect_to_remit: 0,
    cohost_property_flag: 1,
    owner_notes: [],
  },
}

const defaultReport = reportByPropertyId[101]

const formatDate = date => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

const resolveDateWindow = (dateRange, month, year) => {
  const now = new Date()
  const start = new Date(now.getFullYear(), now.getMonth(), 1)
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0)

  if (dateRange === 'specific_month') {
    const resolvedYear = Number(year) || now.getFullYear()
    const resolvedMonth = Number(month) ? Number(month) - 1 : now.getMonth()
    const specificStart = new Date(resolvedYear, resolvedMonth, 1)
    const specificEnd = new Date(resolvedYear, resolvedMonth + 1, 0)

    return {
      start_date: formatDate(specificStart),
      end_date: formatDate(specificEnd),
    }
  }

  const windows = {
    last_month: () => {
      start.setMonth(start.getMonth() - 1)
      end.setTime(new Date(start.getFullYear(), start.getMonth() + 1, 0).getTime())
    },
    last_quarter: () => {
      start.setMonth(start.getMonth() - 3)
      end.setTime(new Date(now.getFullYear(), now.getMonth(), 0).getTime())
    },
    last_year: () => {
      start.setFullYear(now.getFullYear() - 1, 0, 1)
      end.setFullYear(now.getFullYear() - 1, 11, 31)
    },
    last_12_months: () => {
      start.setFullYear(now.getFullYear() - 1, now.getMonth(), 1)
      end.setTime(new Date(now.getFullYear(), now.getMonth(), 0).getTime())
    },
    this_quarter: () => {
      const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3
      start.setMonth(quarterStartMonth, 1)
      end.setMonth(quarterStartMonth + 3, 0)
    },
    this_year: () => {
      start.setMonth(0, 1)
      end.setMonth(11, 31)
    },
    next_month: () => {
      start.setMonth(start.getMonth() + 1, 1)
      end.setTime(new Date(start.getFullYear(), start.getMonth() + 1, 0).getTime())
    },
    next_quarter: () => {
      const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3 + 3
      start.setMonth(quarterStartMonth, 1)
      end.setMonth(quarterStartMonth + 3, 0)
    },
    next_year: () => {
      start.setFullYear(now.getFullYear() + 1, 0, 1)
      end.setFullYear(now.getFullYear() + 1, 11, 31)
    },
  }

  windows[dateRange]?.()

  return {
    start_date: formatDate(start),
    end_date: formatDate(end),
  }
}

export const mockOwnerStatementProperties = () => MOCK_PROPERTIES
  .filter(property => property.status !== 0)
  .slice(0, 6)
  .map(property => ({
    id: property.id,
    property_title: property.property_title,
    street_address: property.street_address,
    tot_mode: property.tot_mode,
    property_cohost: property.property_cohost,
  }))

export const MOCK_OWNER_STATEMENT_META = {
  properties: mockOwnerStatementProperties(),
  date_ranges: OWNER_STATEMENT_DATE_RANGES,
}

export const buildMockOwnerStatementReport = (params = {}) => {
  const propertyId = params.property_id
  const property = MOCK_PROPERTIES.find(item => String(item.id) === String(propertyId))
    || MOCK_OWNER_STATEMENT_META.properties.find(item => String(item.id) === String(propertyId))
    || MOCK_OWNER_STATEMENT_META.properties[0]

  const base = structuredClone(reportByPropertyId[property?.id] || defaultReport)
  const window = resolveDateWindow(params.date_range || 'this_month', params.month, params.year)

  // Light variance by range so filter changes feel responsive in the UI preview.
  const rangeMultiplier = {
    last_week: 0.35,
    last_month: 1,
    last_quarter: 2.6,
    last_year: 9.4,
    last_12_months: 9.1,
    this_month: 1,
    this_quarter: 2.2,
    this_year: 7.8,
    next_month: 0.85,
    next_quarter: 2,
    next_year: 8.5,
    specific_month: 1,
  }[params.date_range || 'this_month'] || 1

  const scaleMoney = value => Number((Number(value || 0) * rangeMultiplier).toFixed(2))
  const scaleCount = value => Math.max(0, Math.round(Number(value || 0) * Math.min(rangeMultiplier, 3)))

  const report = {
    ...base,
    property: property
      ? {
          id: property.id,
          property_title: property.property_title,
          street_address: property.street_address,
          tot_mode: property.tot_mode,
        }
      : null,
    property_title: property?.property_title || 'Preview property',
    street_address: property?.street_address || '',
    start_date: window.start_date,
    end_date: window.end_date,
    date_range: params.date_range || 'this_month',
    total_reservations: scaleCount(base.total_reservations),
    booked_nights: scaleCount(base.booked_nights),
    blocked_nights: scaleCount(base.blocked_nights),
    excluded_linked_bookings_count: scaleCount(base.excluded_linked_bookings_count),
    accomodations: scaleMoney(base.accomodations),
    cleaning_fee: scaleMoney(base.cleaning_fee),
    tot_charged_to_guest: scaleMoney(base.tot_charged_to_guest),
    daily_utility_fee: scaleMoney(base.daily_utility_fee),
    security_deposit_deductions: scaleMoney(base.security_deposit_deductions),
    pet_fee: scaleMoney(base.pet_fee),
    airbnb_cohost_payout: scaleMoney(base.airbnb_cohost_payout),
    accounting_adjustment_income: scaleMoney(base.accounting_adjustment_income),
    safely_insurance_fee: scaleMoney(base.safely_insurance_fee),
    total_guest_paid: scaleMoney(base.total_guest_paid),
    site_listing_fee: scaleMoney(base.site_listing_fee),
    credit_card_transaction_fee: scaleMoney(base.credit_card_transaction_fee),
    payment_of_cleaners: scaleMoney(base.payment_of_cleaners),
    booking_payments: scaleMoney(base.booking_payments),
    property_payments: scaleMoney(base.property_payments),
    concierge_restocking: scaleMoney(base.concierge_restocking),
    management_fee: scaleMoney(base.management_fee),
    maintance: scaleMoney(base.maintance),
    total_paid_by_airconcierge_to_city: scaleMoney(base.total_paid_by_airconcierge_to_city),
    accounting_adjustment: scaleMoney(base.accounting_adjustment),
    owner_payout: scaleMoney(base.owner_payout),
    convenience_fee: scaleMoney(base.convenience_fee),
    management_markup: scaleMoney(base.management_markup),
    total_income: scaleMoney(base.total_income),
    total_expenses: scaleMoney(base.total_expenses),
    net_income: scaleMoney(base.net_income),
    total_elect_to_remit: scaleMoney(base.total_elect_to_remit),
    schema_gaps: [...OWNER_STATEMENT_SCHEMA_GAPS],
  }

  if (property?.tot_mode === 'paid_at_owners_election' && !report.total_elect_to_remit)
    report.total_elect_to_remit = report.tot_charged_to_guest

  if (property?.property_cohost)
    report.cohost_property_flag = 1

  return report
}
