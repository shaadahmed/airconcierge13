import { MOCK_PROPERTIES } from '@/mocks/properties'

/**
 * UI-preview fixtures for admin revenue settings.
 * Combines legacy `revenue_settings` editable rates with owner-stated
 * values from `property_revenue_settings` (nested on properties in mocks).
 */
export const MOCK_REVENUE_SETTINGS = [
  {
    id: 1,
    property_id: 101,
    property: {
      id: 101,
      property_title: 'Pinecrest Cabin',
      property_image_url: null,
    },
    dynamic_pricing: 1,
    base_rate: 310,
    min_rate: 275,
    min_nightly_rate: 275,
    min_nights_per_booking: 2,
    previous_base_rate: 295,
    previous_min_rate: 260,
  },
  {
    id: 2,
    property_id: 103,
    property: {
      id: 103,
      property_title: 'Incline Lakefront Suite',
      property_image_url: null,
    },
    dynamic_pricing: 0,
    base_rate: 420,
    min_rate: 380,
    min_nightly_rate: -1,
    min_nights_per_booking: 3,
    previous_base_rate: 400,
    previous_min_rate: null,
  },
  {
    id: 3,
    property_id: 104,
    property: {
      id: 104,
      property_title: 'Heavenly Village Loft',
      property_image_url: null,
    },
    dynamic_pricing: 1,
    base_rate: 255,
    min_rate: 220,
    min_nightly_rate: 220,
    min_nights_per_booking: 2,
    previous_base_rate: null,
    previous_min_rate: 210,
  },
  {
    id: 4,
    property_id: 102,
    property: {
      id: 102,
      property_title: 'Vineyard View Cottage',
      property_image_url: null,
    },
    dynamic_pricing: 0,
    base_rate: null,
    min_rate: null,
    min_nightly_rate: null,
    min_nights_per_booking: null,
    previous_base_rate: null,
    previous_min_rate: null,
  },
]

/** Property picker options for filter / create forms. */
export const revenuePropertyItems = (extra = []) => {
  const seen = new Set()
  const items = [{ title: 'Select property', value: null }]

  for (const property of [...MOCK_PROPERTIES, ...extra]) {
    if (!property?.id || seen.has(property.id))
      continue

    seen.add(property.id)
    items.push({
      title: property.property_title || `Property #${property.id}`,
      value: property.id,
    })
  }

  return items
}

export const formatRate = value => {
  if (value == null || value === '' || Number(value) === -1)
    return '—'

  const amount = Number(value)
  if (Number.isNaN(amount))
    return '—'

  return `$${amount.toLocaleString(undefined, { maximumFractionDigits: 2 })}`
}

export const formatOwnerValue = value => {
  if (value == null || value === '' || Number(value) === -1)
    return '—'

  return String(value)
}
