/**
 * UI-preview fixtures for countries & states until the API is wired.
 */

export const MOCK_COUNTRIES = [
  {
    id: 1,
    name: 'United States',
    code: 'US',
    lat: 37.09,
    lng: -95.71,
    states: [
      { id: 101, name: 'California', code: 'CA', lat: 36.78, lng: -119.42 },
      { id: 102, name: 'Nevada', code: 'NV', lat: 38.8, lng: -116.42 },
    ],
  },
  {
    id: 2,
    name: 'Canada',
    code: 'CA',
    lat: 56.13,
    lng: -106.35,
    states: [
      { id: 201, name: 'British Columbia', code: 'BC', lat: 53.73, lng: -127.65 },
    ],
  },
]
