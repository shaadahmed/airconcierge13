/**
 * UI-preview fixtures for regions until the API is wired.
 */

export const MOCK_REGIONS = [
  {
    id: 1,
    name: 'Lake Tahoe',
    region_name: 'Lake Tahoe',
    shortcode: 'LT',
    color: '#4caf50',
    subregions: [
      { id: 11, name: 'South Lake Tahoe', subregion_name: 'South Lake Tahoe', region_id: 1 },
      { id: 12, name: 'Incline Village', subregion_name: 'Incline Village', region_id: 1 },
    ],
  },
  {
    id: 2,
    name: 'Napa Valley',
    region_name: 'Napa Valley',
    shortcode: 'NV',
    color: '#ff9800',
    subregions: [
      { id: 21, name: 'Napa', subregion_name: 'Napa', region_id: 2 },
      { id: 22, name: 'St. Helena', subregion_name: 'St. Helena', region_id: 2 },
    ],
  },
  {
    id: 3,
    name: 'Palm Springs',
    region_name: 'Palm Springs',
    shortcode: 'PS',
    color: '#2196f3',
    subregions: [],
  },
]
