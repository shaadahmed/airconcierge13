/**
 * UI-preview fixtures for countries, states, and cities until the API is wired.
 */

export const MOCK_COUNTRIES = [
  {
    id: 1,
    name: 'United States',
    code: 'US',
    lat: 37.09,
    lng: -95.71,
    states: [
      {
        id: 101,
        name: 'California',
        code: 'CA',
        lat: 36.78,
        lng: -119.42,
        cities: [
          { id: 1001, name: 'South Lake Tahoe', code: 'SLT', zip: 96150, contact_code: null },
          { id: 1002, name: 'Napa', code: 'NAPA', zip: 94558, contact_code: null },
          { id: 1003, name: 'Tahoe City', code: 'TC', zip: 96145, contact_code: null },
        ],
      },
      {
        id: 102,
        name: 'Nevada',
        code: 'NV',
        lat: 38.8,
        lng: -116.42,
        cities: [
          { id: 1004, name: 'Incline Village', code: 'IV', zip: 89451, contact_code: null },
          { id: 1005, name: 'Crystal Bay', code: 'CB', zip: 89402, contact_code: null },
        ],
      },
    ],
  },
  {
    id: 2,
    name: 'Canada',
    code: 'CA',
    lat: 56.13,
    lng: -106.35,
    states: [
      {
        id: 201,
        name: 'British Columbia',
        code: 'BC',
        lat: 53.73,
        lng: -127.65,
        cities: [
          { id: 2001, name: 'Vancouver', code: 'VAN', zip: null, contact_code: null },
        ],
      },
    ],
  },
]

/**
 * Resolve a city (with nested state/country) from mock geography data.
 *
 * @param {number|string|null|undefined} cityId
 * @param {Array} [countries=MOCK_COUNTRIES]
 */
export const findCityById = (cityId, countries = MOCK_COUNTRIES) => {
  if (cityId == null || cityId === '')
    return null

  for (const country of countries) {
    for (const state of country.states || []) {
      const city = (state.cities || []).find(item => String(item.id) === String(cityId))
      if (!city)
        continue

      return {
        ...city,
        state_id: state.id,
        state: {
          id: state.id,
          name: state.name,
          code: state.code,
          country_id: country.id,
          country: {
            id: country.id,
            name: country.name,
            code: country.code,
          },
        },
      }
    }
  }

  return null
}

/**
 * Display helpers for properties that may still carry legacy string city/state.
 *
 * @param {object|null|undefined} property
 */
export const propertyCityName = property => {
  if (!property)
    return ''

  if (typeof property.city === 'string')
    return property.city

  return property.city?.name || property.city_name || ''
}

/**
 * @param {object|null|undefined} property
 */
export const propertyStateName = property => {
  if (!property)
    return ''

  if (property.city && typeof property.city === 'object') {
    return property.city.state?.name || property.city.state?.code || ''
  }

  if (typeof property.state === 'string')
    return property.state

  return property.state?.name || property.state_code || ''
}
