/**
 * UI-preview fixtures for regions until the API is wired.
 */

export const MOCK_REGIONS = [
  {
    id: 1,
    name: 'Lake Tahoe',
    code: 'LT',
    color: '#4caf50',
    subregions: [
      { id: 11, name: 'South Lake Tahoe', region_id: 1 },
      { id: 12, name: 'Incline Village', region_id: 1 },
    ],
  },
  {
    id: 2,
    name: 'Napa Valley',
    code: 'NV',
    color: '#ff9800',
    subregions: [
      { id: 21, name: 'Napa', region_id: 2 },
      { id: 22, name: 'St. Helena', region_id: 2 },
    ],
  },
  {
    id: 3,
    name: 'Palm Springs',
    code: 'PS',
    color: '#2196f3',
    subregions: [],
  },
]
