/**
 * Hardcoded admin sidebar while the navigation API / resources table are rebuilt.
 * Structure mirrors the former ResourceSeeder + config/admin_navigation.php (ADR-020).
 */
export const adminNavigationGroups = [
  {
    id: 12,
    title: 'Dashboard',
    icon: 'bx-tachometer',
    children: [
      { title: 'Performance', to: '/admin/dashboard', icon: 'bxs-circle' },
      { title: 'Accounting', to: '/admin/dashboard/accounting', icon: 'bxs-circle' },
      { title: 'Calendar Blocks (Owners)', to: '/admin/dashboard/ownerblock/all', icon: 'bxs-circle' },
      { title: 'Calendar View', to: '/admin/dashboard/ownercalendarview', icon: 'bxs-circle' },
      { title: 'Reservation | City Limit', to: '/admin/dashboard/threshold-properties', icon: 'bxs-circle' },
    ],
  },
  {
    id: 181,
    title: 'Properties',
    icon: 'bx-group',
    children: [
      { title: 'Homes', to: '/admin/properties', icon: 'bxs-circle' },
      { title: 'Owners', to: '/admin/owners', icon: 'bxs-circle' },
      { title: 'Guests', to: '/admin/guests', icon: 'bxs-circle' },
      { title: 'Regions', to: '/admin/regions', icon: 'bxs-circle' },
      { title: 'Audit Reports', to: '/admin/properties/audit/list', icon: 'bxs-circle' },
      { title: 'Countries & States', to: '/admin/countries', icon: 'bxs-circle' },
    ],
  },
  {
    id: 182,
    title: 'Financials',
    icon: 'bx-book',
    children: [
      { title: 'Bookings', to: '/admin/bookings', icon: 'bxs-circle' },
      { title: 'Costs & Expenses', to: '/admin/payments/bookings', icon: 'bxs-circle' },
      { title: 'Revenue Settings', to: '/admin/settings/revenue', icon: 'bxs-circle' },
      { title: 'P&L Owner Statements', to: '/admin/dashboard/ownerstatements', icon: 'bxs-circle' },
      { title: 'Month Closing History', to: '/admin/reports/properties/closing-history', icon: 'bxs-circle' },
      { title: 'Cash Flow', to: '/admin/dashboard/cashflow', icon: 'bxs-circle' },
    ],
  },
  {
    id: 183,
    title: 'Staff',
    icon: 'bx-briefcase',
    children: [
      { title: 'Vendors', to: '/admin/vendors', icon: 'bxs-circle' },
      { title: 'Regional Managers', to: '/admin/managers', icon: 'bxs-circle' },
    ],
  },
  {
    id: 172,
    title: 'Reporting',
    icon: 'bx-bar-chart',
    children: [
      { title: 'Reporting', to: '/admin/reports/properties', icon: 'bxs-circle' },
      { title: 'Nights & Pay Outs', to: '/admin/reports/nightspayouts', icon: 'bxs-circle' },
      { title: 'Difference (Booking vs Stay)', to: '/admin/reports/bookingstay', icon: 'bxs-circle' },
      { title: 'Regions Income', to: '/admin/reports/regionsincome', icon: 'bxs-circle' },
      { title: 'Total Income', to: '/admin/reports/totalincome', icon: 'bxs-circle' },
      { title: 'Guest Location', to: '/admin/reports/guestlocation', icon: 'bxs-circle' },
      { title: 'Owner Block Abandonment', to: '/admin/reports/owner-block-abandonment', icon: 'bxs-circle' },
      { title: 'Reservation | City Limit', to: '/admin/reports/properties/threshold', icon: 'bxs-circle' },
    ],
  },
  {
    id: 'administration',
    title: 'Administration',
    icon: 'bx-cog',
    children: [
      { title: 'Air BNB Emails', to: '/admin/airbnbemails', icon: 'bxs-circle' },
      { title: 'Hostaway Logs', to: '/admin/hostawaylogs', icon: 'bxs-circle' },
      {
        title: 'Email Logs',
        to: '/admin/outbound-email-logs',
        icon: 'bxs-circle',
        badgeContent: 'New',
        badgeClass: 'bg-warning',
      },
      { title: 'Manage Content', to: '/admin/cms', icon: 'bxs-circle' },
      { title: 'Manage Users', to: '/admin/users', icon: 'bxs-circle' },
      { title: 'Manage Roles', to: '/admin/roles', icon: 'bxs-circle' },
      { title: 'Management Types', to: '/admin/management_types', icon: 'bxs-circle' },
      {
        title: 'Payment Types',
        to: '/admin/payment_types',
        icon: 'bxs-circle',
        badgeContent: 'New',
        badgeClass: 'bg-warning',
      },
      { title: 'System Resources', to: '/admin/resources', icon: 'bxs-circle' },
      { title: 'Platforms', to: '/admin/platforms', icon: 'bxs-circle' },
      { title: 'Critical Actions', to: '/admin/settings/cockpit', icon: 'bxs-circle' },
      { title: 'Export Guests', to: '/admin/export/guestemailslist', icon: 'bxs-circle' },
      {
        title: 'System Settings',
        to: '/admin/settings/system',
        icon: 'bxs-circle',
        badgeContent: 'New',
        badgeClass: 'bg-warning',
      },
      { title: 'Cron & database rules', to: '/admin/settings/cron', icon: 'bxs-circle' },
      { title: 'My Profile', to: '/admin/dashboard/profile', icon: 'bxs-circle' },
    ],
  },
]
