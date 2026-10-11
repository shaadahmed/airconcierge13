/**
 * UI-preview fixtures for System Settings administration tabs
 * (management types, payment types, resources, platforms, settings).
 */

export const PAYMENT_TYPE_CATEGORY_OPTIONS = [
  { title: 'Cleaning', value: 'Cleaning' },
  { title: 'Maintenance', value: 'Maintenance' },
  { title: 'Supplies', value: 'Supplies' },
  { title: 'Utilities', value: 'Utilities' },
  { title: 'Owner Payout', value: 'Owner Payout' },
  { title: 'Platform Fee', value: 'Platform Fee' },
  { title: 'Tax', value: 'Tax' },
  { title: 'Other', value: 'Other' },
]

export const SETTING_TYPE_OPTIONS = [
  { title: 'Text', value: 'text' },
  { title: 'Numeric', value: 'numeric' },
  { title: 'Boolean', value: 'boolean' },
  { title: 'JSON', value: 'json' },
  { title: 'Array', value: 'array' },
  { title: 'Email', value: 'email' },
  { title: 'URL', value: 'url' },
  { title: 'Date', value: 'date' },
  { title: 'Time', value: 'time' },
  { title: 'DateTime', value: 'datetime' },
]

export const YES_NO_OPTIONS = [
  { title: 'Yes', value: 1 },
  { title: 'No', value: 0 },
]

export const MOCK_MANAGEMENT_TYPES = [
  { id: 1, name: 'Full Management' },
  { id: 2, name: 'Partial Management' },
  { id: 3, name: 'Owner Managed' },
  { id: 4, name: 'Co-Host' },
]

export const MOCK_PAYMENT_TYPES = [
  {
    id: 1,
    title: 'Standard Cleaning',
    category: 'Cleaning',
    subcategory: 'Turnover',
  },
  {
    id: 2,
    title: 'Deep Clean',
    category: 'Cleaning',
    subcategory: 'Deep',
  },
  {
    id: 3,
    title: 'Plumbing Repair',
    category: 'Maintenance',
    subcategory: 'Plumbing',
  },
  {
    id: 4,
    title: 'Linens Restock',
    category: 'Supplies',
    subcategory: 'Linens',
  },
  {
    id: 5,
    title: 'Airbnb Host Fee',
    category: 'Platform Fee',
    subcategory: 'Airbnb',
  },
  {
    id: 6,
    title: 'TOT Remittance',
    category: 'Tax',
    subcategory: 'TOT',
  },
]

export const MOCK_SYSTEM_RESOURCES = [
  {
    id: 12,
    parent_id: 0,
    parent_name: '—',
    name: 'Dashboard',
    path: 'admin/dashboard',
    description: 'Performance and accounting dashboards',
    link: 1,
    order: 1,
    icon_class: 'bx bx-tachometer',
  },
  {
    id: 181,
    parent_id: 0,
    parent_name: '—',
    name: 'Properties',
    path: 'admin/properties',
    description: 'Homes, owners, guests, regions',
    link: 0,
    order: 2,
    icon_class: 'bx bx-group',
  },
  {
    id: 200,
    parent_id: 181,
    parent_name: 'Properties',
    name: 'Homes',
    path: 'admin/properties',
    description: 'Property list',
    link: 1,
    order: 1,
    icon_class: 'bx bx-home',
  },
  {
    id: 69,
    parent_id: 0,
    parent_name: '—',
    name: 'Administration',
    path: 'admin',
    description: 'Admin tools and settings',
    link: 0,
    order: 10,
    icon_class: 'bx bx-cog',
  },
  {
    id: 95,
    parent_id: 69,
    parent_name: 'Administration',
    name: 'System Settings',
    path: 'admin/settings/system',
    description: 'System configuration',
    link: 1,
    order: 8,
    icon_class: 'bx bx-slider',
  },
]

export const MOCK_PLATFORMS = [
  {
    id: 1,
    platform_name: 'Airbnb',
    hostaway_channel_id: '2018',
    include_in_charts: 1,
  },
  {
    id: 2,
    platform_name: 'VRBO',
    hostaway_channel_id: '2019',
    include_in_charts: 1,
  },
  {
    id: 3,
    platform_name: 'Booking.com',
    hostaway_channel_id: '2020',
    include_in_charts: 1,
  },
  {
    id: 4,
    platform_name: 'Direct Booking',
    hostaway_channel_id: '',
    include_in_charts: 0,
  },
  {
    id: 5,
    platform_name: 'Owner Block',
    hostaway_channel_id: '',
    include_in_charts: 0,
  },
]

export const MOCK_SYSTEM_SETTINGS = [
  {
    id: 1,
    setting_key: 'company_name',
    setting_value: 'Air Concierge',
    type: 'text',
    description: 'Company display name used in emails and statements',
  },
  {
    id: 2,
    setting_key: 'support_email',
    setting_value: 'support@airconcierge.example',
    type: 'email',
    description: 'Primary support contact email',
  },
  {
    id: 3,
    setting_key: 'default_markup',
    setting_value: '12.5',
    type: 'numeric',
    description: 'Default administrative markup percentage',
  },
  {
    id: 4,
    setting_key: 'enable_hostaway_sync',
    setting_value: '1',
    type: 'boolean',
    description: 'Toggle Hostaway calendar and reservation sync',
  },
  {
    id: 5,
    setting_key: 'owner_portal_url',
    setting_value: 'https://owners.airconcierge.example',
    type: 'url',
    description: 'Public owner portal base URL',
  },
  {
    id: 6,
    setting_key: 'statement_cutoff_day',
    setting_value: '2026-10-01',
    type: 'date',
    description: 'Monthly statement cutoff date template',
  },
  {
    id: 7,
    setting_key: 'notification_channels',
    setting_value: '["email","slack"]',
    type: 'json',
    description: 'Enabled outbound notification channels',
  },
]

/** Property payment methods shown under System Settings (legacy Payment Methods card). */
export const MOCK_PROPERTY_PAYMENT_METHODS = [
  {
    id: 1,
    title: 'Credit Card',
    convenience_fee: '3.00',
    management_markup: '2.5',
  },
  {
    id: 2,
    title: 'ACH / Direct Deposit',
    convenience_fee: '0.00',
    management_markup: '0',
  },
  {
    id: 3,
    title: 'PayPal',
    convenience_fee: '2.50',
    management_markup: '1.0',
  },
  {
    id: 4,
    title: 'Check',
    convenience_fee: '0.00',
    management_markup: '0',
  },
]

export const SYSTEM_SETTINGS_TABS = [
  { value: 'management-types', title: 'Management Types' },
  { value: 'payment-types', title: 'Payment Types' },
  { value: 'resources', title: 'System Resources' },
  { value: 'platforms', title: 'Platforms' },
  { value: 'payment-methods', title: 'Payment Methods' },
  { value: 'settings', title: 'Settings' },
]
