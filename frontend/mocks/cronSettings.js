/**
 * UI-preview fixtures for Cron Jobs & Database Rules
 * (sourced from legacy cron-settings + cron_jobs table).
 */

export const CRON_SETTINGS_TABS = [
  { title: 'Cron Jobs', value: 'cron-jobs', icon: 'bx-time-five' },
  { title: 'Database Rules', value: 'database-rules', icon: 'bx-data' },
]

export const MOCK_CRON_JOBS = [
  {
    id: 1,
    title: 'Import Airbnb Email Bookings',
    subtitle: 'Raw information fetched from emails and stored in database without organizing',
    icon: 'bx-envelope',
    is_active: true,
    description: `
      <p>This cron job reads the booking confirmation and cancelation emails received from Airbnb at
      <strong>managedatabase@airconcierge.net</strong> and stores the required information into the
      database in raw form, not visible to the user/UI.</p>
    `,
  },
  {
    id: 2,
    title: 'Process Airbnb Emails',
    subtitle: 'Process Raw emails information and create booking records',
    icon: 'bx-transfer-alt',
    is_active: true,
    description: `
      <p>This cron job reads the raw information stored by import cron from the database and saves them
      as booking records automatically.</p>
      <h4>Procedure</h4>
      <p>For booking confirmation, our CRON bot searches for email subjects from raw data that contain
      <em>Reservation Confirmed</em> and takes that data as a new booking confirmation.</p>
      <p>For booking cancelation, our CRON bot searches for subjects that contain
      <em>Reservation canceled</em> and takes that data as a booking cancelation email.</p>
      <p>Furthermore, on a canceled booking email data our CRON bot search for keywords
      <em>fully refunded</em> or <em>complete refund</em> and marks that booking as canceled with no
      payout and zero out all financials. If those keywords are not found, the bot starts searching for
      keywords <em>payout has been updated</em> or <em>guest was refunded</em>. If any of these
      keywords are found, the booking is marked as canceled with payout.</p>
    `,
  },
  {
    id: 3,
    title: 'Check Recurring Payments',
    subtitle: 'If there is a recurring payment, notify by email',
    icon: 'bx-line-chart',
    is_active: true,
    description: `
      <p>This cron job checks everyday if a recurring payment chain is about to end, then send an email
      alert at <strong>guest_support@airconcierge.net</strong>.</p>
    `,
  },
  {
    id: 4,
    title: 'Password Updation Reminder',
    subtitle: 'Password reset email after 1 year of not updating the password',
    icon: 'bx-key',
    is_active: true,
    description: `
      <p>This cron job checks if a user has not updated their password in a year, then send email
      notification on the user's registered email address asking for a password reset.</p>
    `,
  },
  {
    id: 5,
    title: 'Property Permit Expiration',
    subtitle: 'Check property permit expiration if ending in 60 days then send email',
    icon: 'bx-id-card',
    is_active: true,
    description: `
      <p>This cron job checks if there is a property permit expiring in 60 days, then send email
      reminder at <strong>adminteam@airconcierge.net</strong>.</p>
    `,
  },
  {
    id: 6,
    title: 'Property Vacancy',
    subtitle: 'Check if a property is empty for 7 days or more then send email alert',
    icon: 'bx-home-alt',
    is_active: true,
    description: `
      <p>This cron job checks if an active property has not been booked for 7 or more days then send
      email notification to <strong>guest_support@airconcierge.net</strong>.</p>
      <p>Furthermore, our system checks if an alert has already been sent in the last 7 days then do not
      send the same repetitive alert.</p>
    `,
  },
  {
    id: 7,
    title: 'Long Stay Payment Tracker',
    subtitle: 'Checks if long stays payments have been received',
    icon: 'bx-group',
    is_active: true,
    description: `
      <p>Checks if there is a long term stay at a property and notifies AC to check / ensure rent
      payments have been made, then send two email notifications at
      <strong>billing@airconcierge.net</strong> &amp;
      <strong>CC: guest_support@airconcierge.net</strong>.</p>
      <ul>
        <li>5 days before reservation start date</li>
        <li>2 days after reservation start date</li>
      </ul>
    `,
  },
  {
    id: 8,
    title: 'Audit Reminders',
    subtitle: 'Reminds managers about property visits',
    icon: 'bx-calendar-check',
    is_active: true,
    description: `
      <p><strong>Two Audit Cron Reminders</strong></p>
      <ol>
        <li>An audit reminder is sent if a property is active but audit has not been performed in the past 60 days.</li>
        <li>Another reminder is sent after 10 days again if the property is still not visited even after the initial 60 days reminder.</li>
      </ol>
      <h4>Who receives these emails?</h4>
      <p>These emails are sent to the respective manager of the region/property while the support email
      of that region/property is also being CC'd in such emails.</p>
    `,
  },
  {
    id: 9,
    title: 'Payout Disbursing',
    subtitle: 'Informs owners about payout disbursing.',
    icon: 'bx-wallet',
    is_active: true,
    description: `
      <p>This job sends an email containing owner statements to all active owners. Email is generated on
      the date which was selected by super admin at the time of month closing. For more details of this
      task see month closing rules in the Database Rules section.</p>
    `,
  },
]

/** @typedef {'automatic' | 'manual'} DatabaseRuleType */

/**
 * @type {Array<{
 *   id: string,
 *   title: string,
 *   summary: string,
 *   type: DatabaseRuleType,
 *   pages: Array<{ label: string, to: string }>,
 *   descriptionHtml: string,
 *   matrices?: Array<{
 *     title: string,
 *     columns: string[],
 *     rows: Array<{ case: string, cells: Array<{ symbol: string, note?: string }> }>,
 *   }>,
 * }>}
 */
export const MOCK_DATABASE_RULES = [
  {
    id: 'recurring-payments-popup',
    title: 'Recurring Payments Popup',
    summary: 'Dashboard popup when a recurring payment chain ends today.',
    type: 'automatic',
    pages: [{ label: 'Admin dashboard', to: '/admin/dashboard' }],
    descriptionHtml: `
      <p>If a property has last recurring payment in a chain and today is its payment date, then a popup
      is displayed on dashboard with a list of recurring payments that fall under this criteria.</p>
    `,
  },
  {
    id: 'parent-child-bookings',
    title: 'Parent Child Relation Between Bookings',
    summary: 'Manually link split booking records to a parent booking.',
    type: 'manual',
    pages: [{ label: 'Bookings', to: '/admin/bookings' }],
    descriptionHtml: `
      <p>If a lengthy booking is split into multiple records, admins have to open the child booking and
      use the dropdown <em>"Select if this is part of a series of Bookings"</em> and select the parent
      booking. Further information is available on the field hint.</p>
    `,
  },
  {
    id: 'occupancy-threshold',
    title: 'Property Occupancy Threshold',
    summary: 'Occupancy bar and threshold listing when reservation limits apply.',
    type: 'automatic',
    pages: [{ label: 'Properties', to: '/admin/properties' }],
    descriptionHtml: `
      <p>If a property lies in a subregion where a reservation limit is set, then such property will
      indicate an occupancy bar on the property page and it will also appear in the list of threshold
      properties listing page if occupancy exceeds 75%.</p>
      <p>Limit can be set on two criteria:</p>
      <ul>
        <li>Number of reservations</li>
        <li>Number of nights</li>
      </ul>
      <p>Limit is also based on either of the following:</p>
      <ul>
        <li>Calendar Year</li>
        <li>Permit Year</li>
      </ul>
    `,
  },
  {
    id: 'direct-deposit-cohost',
    title: 'Direct Deposit On Cohost Warning',
    summary: 'Confirmation popup when Direct Deposit is used with Cohost Yes.',
    type: 'automatic',
    pages: [{ label: 'Properties', to: '/admin/properties' }],
    descriptionHtml: `
      <p>If an admin user selects payment method as <strong>Direct Deposit</strong> on a property having
      Cohost option as <strong>Yes</strong>, then the system displays a confirmation popup.</p>
    `,
  },
  {
    id: 'cleaning-fee-override',
    title: 'Cleaning Fee Override Warning',
    summary: 'Popup when cleaning fee exceeds the property standard exit fee.',
    type: 'automatic',
    pages: [
      { label: 'Bookings', to: '/admin/bookings' },
      { label: 'Properties', to: '/admin/properties' },
    ],
    descriptionHtml: `
      <p>If an admin user tries setting cleaning fee greater than the standard exit cleaning fee of a
      property, then the system displays a confirmation popup.</p>
    `,
  },
  {
    id: 'long-term-auto-split',
    title: 'Long Term Booking Auto Split',
    summary: 'Automatic month-boundary split rules for Airbnb bookings.',
    type: 'automatic',
    pages: [
      { label: 'Bookings', to: '/admin/bookings' },
    ],
    descriptionHtml: `
      <p>The system intercepts the bookings received from Airbnb and decides to split the booking if the
      booking is falling into more than one month.</p>
      <p>Following are the rules of a split:</p>
      <ul>
        <li><strong>Cleaning fee</strong>, <strong>Site Listing Fee</strong> and tot amounts
          (<strong>Tot charged to guest</strong> / <strong>Tot Elect to remit</strong> /
          <strong>Tot paid by AC to city</strong>) are added to the first/earliest booking</li>
        <li>Payment of cleaners is added to the last split record of a booking</li>
        <li><strong>Accommodations</strong>, <strong>Management Fee</strong> and
          <strong>Staff commission</strong> are divided between the splits</li>
        <li>The system does not split a booking that is reserved on a property having payment method
          <strong>Co Host - Credit Card</strong></li>
        <li>Each split generates an email, which means a 5 months long booking will be split into 5 and
          the system will generate 5 emails with same title/guest but different amount values</li>
        <li>The system auto links the split up bookings (Select if this is part of a series of Bookings)
          to create a link between the splits and identify them as part of one</li>
        <li>If the reservation end date is within first 8 days of a month, the system will not split such
          record</li>
        <li>Reservations starting on the last three days of any month, regardless of any other Split
          rule, the booking will be entered into the Next month, unless manually overridden by an admin
          user</li>
      </ul>
    `,
  },
  {
    id: 'month-closing',
    title: 'Month Closing',
    summary: 'Super-admin month close with create/update/delete confirmation emails.',
    type: 'manual',
    pages: [
      { label: 'Bookings', to: '/admin/bookings' },
      { label: 'Owner blocks', to: '/admin/dashboard' },
      { label: 'Payments', to: '/admin/payments/bookings' },
      { label: 'System settings', to: '/admin/settings/system' },
    ],
    descriptionHtml: `
      <p>Super admin can close current month by choosing month from calendar and payout disbursing date
      (to the best of the knowledge of super admin). This page is only accessible to super admin.</p>
      <h4>CREATE (Booking, Owner Block, Cost/Expense)</h4>
      <p>If any new booking, cost/expense or owner block is created that belongs to a closed month,
      system asks for confirmation. If accepted it generates an email.</p>
      <p><strong>To:</strong> billing@airconcierge.net, adminteam@airconcierge.net<br/>
      <strong>BCC:</strong> ryan@airconcierge.net, customercare@airconcierge.net<br/>
      Email contains the link of record that was created.</p>
      <h4>DELETE (Booking, Owner Block, Cost/Expense)</h4>
      <p>If any booking, cost/expense or owner block is deleted that belongs to a closed month, system
      asks for confirmation. If accepted it generates an email.</p>
      <p><strong>To:</strong> billing@airconcierge.net, adminteam@airconcierge.net<br/>
      <strong>BCC:</strong> ryan@airconcierge.net, customercare@airconcierge.net<br/>
      Email contains the link of record that was deleted.</p>
      <h4>UPDATE (Booking, Owner Block, Cost/Expense)</h4>
      <p>If any existing booking, cost/expense or owner block is edited that belongs to a closed month,
      system asks for confirmation. If accepted it generates an email. In this case any change in any
      field triggers this rule.</p>
      <p>If any existing booking, cost/expense or owner block is edited that belongs to an unclosed month,
      however month is being changed to a closed one, the system asks for confirmation and if accepted
      generates an email.</p>
      <p><strong>To:</strong> billing@airconcierge.net, adminteam@airconcierge.net<br/>
      <strong>BCC:</strong> ryan@airconcierge.net, customercare@airconcierge.net<br/>
      Email contains all the fields which are modified and link of record.</p>
    `,
  },
  {
    id: 'owner-block-modification',
    title: 'Owner Block Modification Rules',
    summary: 'Allowed owner-block edits based on cleaner payment and date proximity.',
    type: 'manual',
    pages: [{ label: 'Owner blocks', to: '/admin/dashboard' }],
    descriptionHtml: `
      <p>Owner block edits are constrained by whether payment to cleaner is zero or greater than zero,
      and by how close the start/end dates are to today.</p>
      <p>Legend: <strong>✓</strong> allowed · <strong>✗</strong> not allowed · <strong>⛔</strong> action denied</p>
    `,
    matrices: [
      {
        title: 'Condition 1: Payment to cleaner is 0',
        columns: [
          'Case',
          'Change Cleaning Post Owner Stay',
          'Change Start Date',
          'Change End Date',
        ],
        rows: [
          {
            case: 'Start date is far',
            cells: [{ symbol: '✓' }, { symbol: '✓' }, { symbol: '✓' }],
          },
          {
            case: 'Start date within 2 days',
            cells: [{ symbol: '✓' }, { symbol: '✓' }, { symbol: '✓' }],
          },
          {
            case: 'Start date passed, End date is far',
            cells: [
              { symbol: '✓' },
              { symbol: '✗' },
              { symbol: '✓', note: 'cannot set end date which is past' },
            ],
          },
          {
            case: 'Start date passed, End date within 2 days',
            cells: [
              { symbol: '✗' },
              { symbol: '✗' },
              { symbol: '✓', note: 'cannot set end date which is past' },
            ],
          },
          {
            case: 'Start date passed, End date passed',
            cells: [
              { symbol: '⛔', note: 'Action denied' },
              { symbol: '⛔', note: 'Action denied' },
              { symbol: '⛔', note: 'Action denied' },
            ],
          },
        ],
      },
      {
        title: 'Condition 2: Payment to cleaner is > 0',
        columns: [
          'Case',
          'Change Cleaning Post Owner Stay',
          'Change Start Date',
          'Change End Date',
        ],
        rows: [
          {
            case: 'Start date is far',
            cells: [{ symbol: '✓' }, { symbol: '✓' }, { symbol: '✓' }],
          },
          {
            case: 'Start date within 2 days',
            cells: [{ symbol: '✓' }, { symbol: '✓' }, { symbol: '✓' }],
          },
          {
            case: 'Start date passed, End date is far',
            cells: [
              { symbol: '✓' },
              { symbol: '✗' },
              { symbol: '✓', note: 'cannot set end date within 2 days or past date' },
            ],
          },
          {
            case: 'Start date passed, End date within 2 days',
            cells: [
              { symbol: '✗' },
              { symbol: '✗' },
              { symbol: '✓', note: 'cannot set end date which is past' },
            ],
          },
          {
            case: 'Start date passed, End date passed',
            cells: [
              { symbol: '⛔', note: 'Action denied' },
              { symbol: '⛔', note: 'Action denied' },
              { symbol: '⛔', note: 'Action denied' },
            ],
          },
        ],
      },
    ],
  },
]
