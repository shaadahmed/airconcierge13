/**
 * Canonical UI colors (ADR-022).
 * Use normal for solid fills / icons / emphasis; light for tonal chips / soft chart segments.
 */

/** @typedef {{ normal: string, light: string }} ColorPair */

/** Color 1 — Primary */
export const COLOR_PRIMARY = Object.freeze({ normal: '#6865FF', light: '#E7E6FF' })

/** Color 2 — Secondary / owner block */
export const COLOR_SECONDARY = Object.freeze({ normal: '#6c757d', light: '#DFE2E3' })

/** Color 3 — Success / guest booking */
export const COLOR_SUCCESS = Object.freeze({ normal: '#16A34A', light: '#DCFCE7' })

/** Color 4 — Warning / cancelled booking */
export const COLOR_WARNING = Object.freeze({ normal: '#D97706', light: '#FEF3C7' })

/** Color 5 — Error / danger (cancel & delete) */
export const COLOR_ERROR = Object.freeze({ normal: '#DC3545', light: '#FDF2F2' })

export const UI_PALETTE = Object.freeze({
  primary: COLOR_PRIMARY,
  secondary: COLOR_SECONDARY,
  success: COLOR_SUCCESS,
  warning: COLOR_WARNING,
  error: COLOR_ERROR,
})

/** Guest booking (confirmed reservation) */
export const BOOKING_STATUS_GUEST = COLOR_SUCCESS.normal

/** Owner block */
export const BOOKING_STATUS_OWNER_BLOCK = COLOR_SECONDARY.normal

/** Cancelled booking (solid chips / KPIs) */
export const BOOKING_STATUS_CANCELLED = COLOR_WARNING.normal

/** Cancelled booking on calendar bars (danger light fill + dark label) */
export const BOOKING_STATUS_CANCELLED_CALENDAR = COLOR_ERROR.light

export const BOOKING_STATUS_COLORS = Object.freeze({
  guest: BOOKING_STATUS_GUEST,
  confirmed: BOOKING_STATUS_GUEST,
  block: BOOKING_STATUS_OWNER_BLOCK,
  ownerBlock: BOOKING_STATUS_OWNER_BLOCK,
  cancelled: BOOKING_STATUS_CANCELLED,
})

/** Calendar / filter legend rows */
export const BOOKING_STATUS_LEGEND = Object.freeze([
  { key: 'confirmed', label: 'Guest booking', color: BOOKING_STATUS_GUEST },
  { key: 'block', label: 'Owner block', color: BOOKING_STATUS_OWNER_BLOCK },
  { key: 'cancelled', label: 'Cancelled booking', color: BOOKING_STATUS_CANCELLED_CALENDAR },
])

/** Owner-block cleaning donut: Yes / No (light variants) */
export const OWNER_BLOCK_CLEANING_CHART_COLORS = Object.freeze([
  COLOR_SUCCESS.light,
  COLOR_SECONDARY.light,
])

/**
 * Button intents (BaseButton).
 * - create / Add New → primary filled, white text
 * - save / Update → success filled, white text
 * - cancel / delete → white bg, error border + text
 */
export const BUTTON_INTENTS = Object.freeze({
  create: { color: 'primary', variant: 'elevated' },
  save: { color: 'success', variant: 'elevated' },
  cancel: { color: 'error', variant: 'outlined' },
  delete: { color: 'error', variant: 'outlined' },
})

/**
 * @param {string|undefined|null} label
 * @returns {'create'|'save'|'cancel'|'delete'|null}
 */
export const inferButtonIntent = label => {
  const text = String(label || '').trim().toLowerCase()
  if (!text)
    return null

  if (text === 'cancel' || text.startsWith('cancel '))
    return 'cancel'

  if (text === 'delete' || text.startsWith('delete '))
    return 'delete'

  if (
    text.startsWith('add new')
    || text.startsWith('create')
    || text === 'add'
  )
    return 'create'

  if (text.startsWith('save') || text.startsWith('update'))
    return 'save'

  return null
}

/**
 * @param {'guest'|'confirmed'|'block'|'ownerBlock'|'cancelled'|string} kind
 * @returns {string}
 */
export const bookingStatusHex = kind => {
  if (kind === 'block' || kind === 'ownerBlock' || kind === 'owner block')
    return BOOKING_STATUS_OWNER_BLOCK

  if (kind === 'cancelled')
    return BOOKING_STATUS_CANCELLED

  return BOOKING_STATUS_GUEST
}
