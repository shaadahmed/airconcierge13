/**
 * UI-preview fixtures for bookings until the API is wired.
 */

export const MOCK_BOOKINGS = [
  {
    id: 1001,
    booking_code: 'AC-OVV-2501',
    property_id: 201,
    property: {
      id: 201,
      property_title: 'Ocean View Villa',
      property_image_url: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=200&h=200&fit=crop',
    },
    location: 'Los Angeles, CA',
    status: 'active',
    nightly_rate: 250,
    cancelled_booking: false,
    reservation_start_date: '2026-04-12',
    reservation_end_date: '2026-04-16',
    booking_date: '2026-03-01',
    no_of_guests: 4,
    guest_name: 'Alex Morgan',
    guest_email: 'alex.morgan@example.com',
    platform: 'Airbnb',
    owner_notes: 'Early check-in requested if turnover allows.',
    booking_notes: 'Anniversary stay — leave welcome wine.',
    created_at: '2026-03-01T14:20:00Z',
  },
  {
    id: 1002,
    booking_code: 'AC-SUN-2502',
    property_id: 202,
    property: {
      id: 202,
      property_title: 'Sunset Apartments',
      property_image_url: 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=200&h=200&fit=crop',
    },
    location: 'San Diego, CA',
    status: 'active',
    nightly_rate: 180,
    cancelled_booking: false,
    reservation_start_date: '2026-04-18',
    reservation_end_date: '2026-04-21',
    booking_date: '2026-03-05',
    no_of_guests: 2,
    guest_name: 'Jordan Lee',
    guest_email: 'jordan.lee@example.com',
    platform: 'VRBO',
    owner_notes: '',
    booking_notes: 'Self check-in via lockbox.',
    created_at: '2026-03-05T09:10:00Z',
  },
  {
    id: 1003,
    booking_code: 'AC-PAL-2503',
    property_id: 203,
    property: {
      id: 203,
      property_title: 'The Palm Residence',
      property_image_url: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=200&h=200&fit=crop',
    },
    location: 'Miami, FL',
    status: 'pending',
    nightly_rate: 220,
    cancelled_booking: false,
    reservation_start_date: '2026-05-02',
    reservation_end_date: '2026-05-07',
    booking_date: '2026-03-10',
    no_of_guests: 6,
    guest_name: 'Sam Rivera',
    guest_email: 'sam.rivera@example.com',
    platform: 'Airbnb',
    owner_notes: 'Awaiting owner approval for 6 guests.',
    booking_notes: 'Inquiry converted; payment pending.',
    created_at: '2026-03-10T16:45:00Z',
  },
  {
    id: 1004,
    booking_code: 'AC-BAY-2504',
    property_id: 204,
    property: {
      id: 204,
      property_title: 'Bayview Condo',
      property_image_url: 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=200&h=200&fit=crop',
    },
    location: 'San Francisco, CA',
    status: 'active',
    nightly_rate: 300,
    cancelled_booking: false,
    reservation_start_date: '2026-04-25',
    reservation_end_date: '2026-04-28',
    booking_date: '2026-03-12',
    no_of_guests: 3,
    guest_name: 'Casey Nguyen',
    guest_email: 'casey.nguyen@example.com',
    platform: 'Direct',
    owner_notes: 'Preferred parking stall #12.',
    booking_notes: '',
    created_at: '2026-03-12T11:30:00Z',
  },
  {
    id: 1005,
    booking_code: 'AC-PCT-2505',
    property_id: 101,
    property: {
      id: 101,
      property_title: 'Pinecrest Cabin',
      property_image_url: 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=200&h=200&fit=crop',
    },
    location: 'South Lake Tahoe, CA',
    status: 'active',
    nightly_rate: 275,
    cancelled_booking: false,
    reservation_start_date: '2026-04-08',
    reservation_end_date: '2026-04-11',
    booking_date: '2026-02-20',
    no_of_guests: 5,
    guest_name: 'Taylor Brooks',
    guest_email: 'taylor.brooks@example.com',
    platform: 'Airbnb',
    owner_notes: 'Ski week — confirm hot tub ready.',
    booking_notes: 'Bring pack-n-play on request.',
    created_at: '2026-02-20T18:00:00Z',
  },
  {
    id: 1006,
    booking_code: 'AC-VVC-2506',
    property_id: 102,
    property: {
      id: 102,
      property_title: 'Vineyard View Cottage',
      property_image_url: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=200&h=200&fit=crop',
    },
    location: 'Napa, CA',
    status: 'cancelled',
    nightly_rate: 310,
    cancelled_booking: true,
    reservation_start_date: '2026-03-20',
    reservation_end_date: '2026-03-23',
    booking_date: '2026-01-15',
    no_of_guests: 2,
    guest_name: 'Riley Quinn',
    guest_email: 'riley.quinn@example.com',
    platform: 'VRBO',
    owner_notes: 'Guest cancelled due to travel change.',
    booking_notes: 'Full refund issued.',
    created_at: '2026-01-15T08:25:00Z',
  },
  {
    id: 1007,
    booking_code: 'AC-ILS-2507',
    property_id: 103,
    property: {
      id: 103,
      property_title: 'Incline Lakefront Suite',
      property_image_url: 'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?w=200&h=200&fit=crop',
    },
    location: 'Incline Village, NV',
    status: 'pending',
    nightly_rate: 420,
    cancelled_booking: false,
    reservation_start_date: '2026-05-15',
    reservation_end_date: '2026-05-20',
    booking_date: '2026-03-18',
    no_of_guests: 8,
    guest_name: 'Morgan Ellis',
    guest_email: 'morgan.ellis@example.com',
    platform: 'Airbnb',
    owner_notes: 'Large party — confirm HOA guest rules.',
    booking_notes: 'Security deposit hold pending.',
    created_at: '2026-03-18T13:05:00Z',
  },
  {
    id: 1008,
    booking_code: 'AC-HVL-2508',
    property_id: 104,
    property: {
      id: 104,
      property_title: 'Heavenly Village Loft',
      property_image_url: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=200&h=200&fit=crop',
    },
    location: 'South Lake Tahoe, CA',
    status: 'active',
    nightly_rate: 195,
    cancelled_booking: false,
    reservation_start_date: '2026-04-03',
    reservation_end_date: '2026-04-06',
    booking_date: '2026-02-28',
    no_of_guests: 2,
    guest_name: 'Jamie Patel',
    guest_email: 'jamie.patel@example.com',
    platform: 'Direct',
    owner_notes: '',
    booking_notes: 'Walkable to gondola.',
    created_at: '2026-02-28T20:40:00Z',
  },
]

export const findMockBooking = id => MOCK_BOOKINGS.find(booking => String(booking.id) === String(id)) || null

export const bookingPropertyTitle = booking =>
  booking?.property?.property_title || booking?.property?.name || booking?.property_title || 'Untitled property'

export const bookingLocation = booking => booking?.location || '—'

export const bookingStatusLabel = booking => {
  if (booking?.cancelled_booking || booking?.status === 'cancelled')
    return 'Cancelled'
  if (booking?.status === 'pending')
    return 'Pending'
  if (booking?.status === 'active')
    return 'Active'

  return booking?.cancelled_booking ? 'Cancelled' : 'Active'
}

export const bookingStatusColor = booking => {
  const label = bookingStatusLabel(booking).toLowerCase()

  if (label === 'cancelled')
    return 'error'
  if (label === 'pending')
    return 'info'

  return 'success'
}

export const bookingNightlyRateLabel = booking => {
  const rate = Number(booking?.nightly_rate)
  if (!Number.isFinite(rate))
    return '—'

  return `$${rate} / night`
}
