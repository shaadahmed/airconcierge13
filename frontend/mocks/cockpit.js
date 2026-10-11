/**
 * UI-preview fixtures for Critical Actions (admin cockpit)
 * until month-close / owner-agreement APIs are connected.
 */

const MONTH_NAMES = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
]

export const MONTH_SHORT_LABELS = [
  'Jan',
  'Feb',
  'Mar',
  'Apr',
  'May',
  'Jun',
  'Jul',
  'Aug',
  'Sep',
  'Oct',
  'Nov',
  'Dec',
]

export const MONTH_OPTIONS = MONTH_NAMES.map((title, index) => ({
  title,
  value: index + 1,
}))

/** Suggested closing month (previous calendar month relative to “today” in preview). */
export const MOCK_CLOSING_MONTH = {
  year: 2026,
  month: 9,
  label: 'September',
}

export const MOCK_MONTH_CLOSING_YEARS = [2026, 2025, 2024]

export const MOCK_MONTH_CLOSING_HISTORY = [
  {
    id: 1,
    year: 2026,
    month: 8,
    closed_month: 'Aug 2026',
    closing_date: '09/02/2026',
    payout_date: '09/05/2026',
    closed_by: 'Alex Rivera',
  },
  {
    id: 2,
    year: 2026,
    month: 7,
    closed_month: 'Jul 2026',
    closing_date: '08/03/2026',
    payout_date: '08/06/2026',
    closed_by: 'Alex Rivera',
  },
  {
    id: 3,
    year: 2026,
    month: 6,
    closed_month: 'Jun 2026',
    closing_date: '07/02/2026',
    payout_date: '07/07/2026',
    closed_by: 'Jordan Lee',
  },
  {
    id: 4,
    year: 2025,
    month: 12,
    closed_month: 'Dec 2025',
    closing_date: '01/03/2026',
    payout_date: '01/08/2026',
    closed_by: 'Alex Rivera',
  },
  {
    id: 5,
    year: 2025,
    month: 11,
    closed_month: 'Nov 2025',
    closing_date: '12/02/2025',
    payout_date: '12/05/2025',
    closed_by: 'Jordan Lee',
  },
]

/** When true, the close-month form is replaced by the “already closed” status panel. */
export const MOCK_IS_CURRENT_MONTH_CLOSED = false

export const MOCK_CURRENT_MONTH_CLOSING = {
  month_label: 'October',
  closed_by: 'Alex Rivera',
  closed_by_user_id: 1,
  closing_date: '10/03/2026',
  payout_date: '10/08/2026',
}

export function monthLabel(monthNumber) {
  const index = Number(monthNumber) - 1

  return MONTH_NAMES[index] || '—'
}
