/**
 * UI-preview fixtures for owner calendar blocks until the API is wired.
 */

import { MOCK_PROPERTIES } from './properties.js'

const propertyById = id => MOCK_PROPERTIES.find(property => property.id === id) || null

const withProperty = (id, extras = {}) => {
  const property = propertyById(id)

  return {
    property_id: id,
    property: property
      ? {
          id: property.id,
          property_title: property.property_title,
          street_address: property.street_address,
          property_image_url: property.property_image_url,
          region_id: property.region_id,
          subregion_id: property.subregion_id,
        }
      : { id, property_title: `Property #${id}`, street_address: '', property_image_url: null },
    property_title: property?.property_title || `Property #${id}`,
    street_address: property?.street_address || '',
    location: property
      ? `${property.subregion?.name || property.city?.name || '—'}, ${property.city?.state?.code || ''}`.trim()
      : '—',
    region_id: property?.region_id ?? null,
    subregion_id: property?.subregion_id ?? null,
    ...extras,
  }
}

export const MOCK_OWNER_BLOCKS = [
  {
    id: 501,
    ...withProperty(101, {
      reservation_start_date: '2026-10-12',
      reservation_end_date: '2026-10-14',
      no_of_nights: 2,
      payment_of_cleaners: 185,
      exit_cleaning: '1',
      booking_notes: 'Maintenance window.',
      first_name: 'Jordan',
      last_name: 'Lee',
      email: 'jordan.lee@example.com',
      month: 10,
      year: 2026,
      created_at: '2026-10-01T09:00:00Z',
      latest_log: 'Created by Jordan Lee',
      deleted_at: null,
    }),
  },
  {
    id: 502,
    ...withProperty(102, {
      reservation_start_date: '2026-10-20',
      reservation_end_date: '2026-10-25',
      no_of_nights: 5,
      payment_of_cleaners: 0,
      exit_cleaning: '0',
      booking_notes: 'Owner personal use.',
      first_name: 'Sam',
      last_name: 'Rivera',
      email: 'sam.rivera@example.com',
      month: 10,
      year: 2026,
      created_at: '2026-09-28T12:00:00Z',
      latest_log: 'Updated cleaning preference',
      deleted_at: null,
    }),
  },
  {
    id: 503,
    ...withProperty(101, {
      reservation_start_date: '2026-11-01',
      reservation_end_date: '2026-11-08',
      no_of_nights: 7,
      payment_of_cleaners: 185,
      exit_cleaning: '1',
      booking_notes: 'Holiday family stay.',
      first_name: 'Jordan',
      last_name: 'Lee',
      email: 'jordan.lee@example.com',
      month: 11,
      year: 2026,
      created_at: '2026-09-15T16:30:00Z',
      latest_log: 'Created by Jordan Lee',
      deleted_at: null,
    }),
  },
  {
    id: 504,
    ...withProperty(103, {
      reservation_start_date: '2026-04-18',
      reservation_end_date: '2026-04-22',
      no_of_nights: 4,
      payment_of_cleaners: 220,
      exit_cleaning: '1',
      booking_notes: '',
      first_name: 'Alex',
      last_name: 'Chen',
      email: 'alex.chen@example.com',
      month: 4,
      year: 2026,
      created_at: '2026-03-20T11:00:00Z',
      latest_log: 'Deleted (soft)',
      deleted_at: '2026-03-22T08:00:00Z',
    }),
  },
]

export const findMockOwnerBlock = id =>
  MOCK_OWNER_BLOCKS.find(block => String(block.id) === String(id)) || null

export const ownerBlockPropertyTitle = block =>
  block?.property?.property_title
  || block?.property_title
  || 'Untitled property'

export const ownerBlockAddress = block =>
  block?.property?.street_address
  || block?.street_address
  || block?.location
  || '—'

export const ownerBlockLocation = block => block?.location || ownerBlockAddress(block)

export const ownerBlockExitCleaningLabel = block => {
  const value = block?.exit_cleaning ?? block?.cleaning_post_order_stay
  if (value === true || value === 1 || value === '1')
    return 'Yes'
  if (value === false || value === 0 || value === '0')
    return 'No'

  return '—'
}

export const ownerBlockHasPostStayCleaning = block => {
  const value = block?.exit_cleaning ?? block?.cleaning_post_order_stay

  return value === true || value === 1 || value === '1'
}
