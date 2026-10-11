/**
 * UI-preview fixtures for regional managers until the API is wired.
 */

export const MOCK_MANAGERS = [
  {
    id: 11,
    first_name: 'Pat',
    last_name: 'Rivera',
    email: 'pat.rivera@example.com',
    phone: '530-555-0201',
    is_active: 1,
    salary_type: 1,
    employment_status: 0,
    home_address: '120 Alpine Way',
    compensation_structure: 'Base salary plus booking commission',
    city: 'South Lake Tahoe',
    state: 'California',
    zip: '96150',
    regions: [
      { id: 1, region_name: 'Lake Tahoe' },
    ],
  },
  {
    id: 12,
    first_name: 'Chris',
    last_name: 'Nguyen',
    email: 'chris.nguyen@example.com',
    phone: '707-555-0212',
    is_active: 1,
    salary_type: 0,
    employment_status: 0,
    home_address: '88 Vine Street',
    compensation_structure: 'Salary only',
    city: 'Napa',
    state: 'California',
    zip: '94559',
    regions: [
      { id: 2, region_name: 'Napa Valley' },
    ],
  },
  {
    id: 13,
    first_name: 'Sam',
    last_name: 'Ortiz',
    email: 'sam.ortiz@example.com',
    phone: '775-555-0233',
    is_active: 0,
    salary_type: 2,
    employment_status: 1,
    home_address: '45 Ridge Road',
    compensation_structure: 'Commission only — 8% of management fee',
    city: 'Incline Village',
    state: 'Nevada',
    zip: '89451',
    regions: [
      { id: 1, region_name: 'Lake Tahoe' },
      { id: 2, region_name: 'Napa Valley' },
    ],
  },
]
