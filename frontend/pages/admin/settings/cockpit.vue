<script setup>
import {
  MOCK_CLOSING_MONTH,
  MOCK_CURRENT_MONTH_CLOSING,
  MOCK_IS_CURRENT_MONTH_CLOSED,
  MOCK_MONTH_CLOSING_HISTORY,
  MOCK_MONTH_CLOSING_YEARS,
  MONTH_SHORT_LABELS,
  monthLabel,
} from '@/mocks/cockpit'

definePageMeta({ middleware: 'auth' })

const usingMocks = true
const successMessage = ref('')
const formError = ref('')

const isCurrentMonthClosed = ref(MOCK_IS_CURRENT_MONTH_CLOSED)
const currentMonthClosing = ref(structuredClone(MOCK_CURRENT_MONTH_CLOSING))
const historyRows = ref(structuredClone(MOCK_MONTH_CLOSING_HISTORY))

const closeForm = reactive({
  year: MOCK_CLOSING_MONTH.year,
  month: MOCK_CLOSING_MONTH.month,
  payout_date: null,
})

const historyYear = ref(new Date().getFullYear())

const confirmDialog = reactive({
  open: false,
  kind: null,
})

const yearOptions = computed(() => (
  MOCK_MONTH_CLOSING_YEARS.map(year => ({ title: String(year), value: year }))
))

const selectedMonthName = computed(() => monthLabel(closeForm.month))

const todayStart = computed(() => {
  const today = new Date()

  today.setHours(0, 0, 0, 0)

  return today
})

const payoutMinDate = computed(() => {
  const today = todayStart.value

  return [
    today.getFullYear(),
    String(today.getMonth() + 1).padStart(2, '0'),
    String(today.getDate()).padStart(2, '0'),
  ].join('-')
})

const filteredHistory = computed(() => {
  const year = Number(historyYear.value)

  if (!year)
    return historyRows.value

  return historyRows.value.filter(row => Number(row.year) === year)
})

const flash = message => {
  successMessage.value = message
  formError.value = ''
}

const toDateValue = value => {
  if (!value)
    return null

  if (value instanceof Date)
    return Number.isNaN(value.getTime()) ? null : value

  const parsed = new Date(`${value}T00:00:00`)

  return Number.isNaN(parsed.getTime()) ? null : parsed
}

const formatDateValue = value => {
  const date = toDateValue(value)

  if (!date)
    return '—'

  return date.toLocaleDateString('en-US', {
    month: '2-digit',
    day: '2-digit',
    year: 'numeric',
  })
}

const shiftClosingYear = delta => {
  closeForm.year = Number(closeForm.year) + delta
}

const selectClosingMonth = monthNumber => {
  closeForm.month = monthNumber
}

const openConfirm = kind => {
  formError.value = ''

  if (kind === 'close-month') {
    if (!closeForm.month) {
      formError.value = 'Please select a month that you want to close.'

      return
    }

    if (!toDateValue(closeForm.payout_date)) {
      formError.value = 'Please select payout disbursement date.'

      return
    }

    const payout = toDateValue(closeForm.payout_date)

    if (payout && payout < todayStart.value) {
      formError.value = 'Payout disbursement date must be today or a future date.'

      return
    }
  }

  confirmDialog.kind = kind
  confirmDialog.open = true
}

const closeConfirm = () => {
  confirmDialog.open = false
}

const confirmTitle = computed(() => {
  switch (confirmDialog.kind) {
    case 'close-month':
      return 'Close Month Confirmation'
    case 'enable-agreement':
      return 'Are you sure?'
    case 'send-invitations':
      return 'Send Invitations'
    default:
      return 'Confirm'
  }
})

const confirmLabel = computed(() => {
  switch (confirmDialog.kind) {
    case 'enable-agreement':
      return 'Yes I am sure'
    case 'send-invitations':
      return 'Confirm'
    case 'close-month':
      return 'Confirm'
    default:
      return 'Confirm'
  }
})

const confirmColor = 'primary'

const runConfirmedAction = () => {
  const kind = confirmDialog.kind

  if (kind === 'close-month') {
    const closedMonth = `${selectedMonthName.value.slice(0, 3)} ${closeForm.year}`
    const closingDate = formatDateValue(new Date())
    const payoutDate = formatDateValue(closeForm.payout_date)

    historyRows.value = [
      {
        id: Date.now(),
        year: closeForm.year,
        month: closeForm.month,
        closed_month: closedMonth,
        closing_date: closingDate,
        payout_date: payoutDate,
        closed_by: 'You (preview)',
      },
      ...historyRows.value,
    ]

    isCurrentMonthClosed.value = true
    currentMonthClosing.value = {
      month_label: selectedMonthName.value,
      closed_by: 'You (preview)',
      closed_by_user_id: null,
      closing_date: closingDate,
      payout_date: payoutDate,
    }

    flash(`${selectedMonthName.value} marked as closed (UI preview).`)
  }
  else if (kind === 'enable-agreement') {
    flash('Owner agreement screen enabled (UI preview).')
  }
  else if (kind === 'send-invitations') {
    flash('Terms update invitations queued for active owners (UI preview).')
  }

  closeConfirm()
}
</script>

<template>
  <div class="cockpit-page">
    <PageHeader
      title="Critical Actions"
      subtitle="Month close, owner agreements, and terms update emails"
    />

    <VAlert
      v-if="usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Showing UI preview data until the cockpit APIs are connected.
    </VAlert>

    <VAlert
      v-if="successMessage"
      type="success"
      variant="tonal"
      density="compact"
      closable
      class="mb-4"
      @click:close="successMessage = ''"
    >
      {{ successMessage }}
    </VAlert>

    <VAlert
      v-if="formError"
      type="error"
      variant="tonal"
      density="compact"
      closable
      class="mb-4"
      @click:close="formError = ''"
    >
      {{ formError }}
    </VAlert>

    <VRow>
      <!-- Close month -->
      <VCol
        cols="12"
        lg="8"
      >
        <VCard class="mb-4">
          <template v-if="!isCurrentMonthClosed">
            <VCardItem>
              <template #prepend>
                <div class="cockpit-action-icon">
                  <VIcon
                    icon="bx-calendar-check"
                    size="22"
                  />
                </div>
              </template>
              <VCardTitle>
                Close month of {{ selectedMonthName }}
              </VCardTitle>
              <VCardSubtitle>
                Select the closing month and payout disbursement date
              </VCardSubtitle>
              <template #append>
                <BaseButton
                  variant="tonal"
                  size="small"
                  label="View history"
                  href="#month-closing-history"
                />
              </template>
            </VCardItem>

            <VDivider />

            <VCardText>
              <VAlert
                type="info"
                variant="tonal"
                density="compact"
                class="mb-4"
              >
                Use this date for when, to the best of your knowledge, owner payouts will be disbursed.
              </VAlert>

              <div class="cockpit-calendars">
                <div class="cockpit-calendar-panel">
                  <div class="cockpit-calendar-panel__label">
                    Closing month
                  </div>
                  <div class="month-picker">
                    <div class="month-picker__year">
                      <button
                        type="button"
                        class="month-picker__nav"
                        aria-label="Previous year"
                        @click="shiftClosingYear(-1)"
                      >
                        <VIcon
                          icon="bx-chevron-left"
                          size="20"
                        />
                      </button>
                      <span class="month-picker__year-label">{{ closeForm.year }}</span>
                      <button
                        type="button"
                        class="month-picker__nav"
                        aria-label="Next year"
                        @click="shiftClosingYear(1)"
                      >
                        <VIcon
                          icon="bx-chevron-right"
                          size="20"
                        />
                      </button>
                    </div>
                    <div class="month-picker__grid">
                      <button
                        v-for="(label, index) in MONTH_SHORT_LABELS"
                        :key="label"
                        type="button"
                        class="month-picker__month"
                        :class="{ 'month-picker__month--selected': closeForm.month === index + 1 }"
                        @click="selectClosingMonth(index + 1)"
                      >
                        {{ label }}
                      </button>
                    </div>
                  </div>
                </div>

                <div class="cockpit-calendar-panel">
                  <div class="cockpit-calendar-panel__label">
                    Payout disbursement date
                  </div>
                  <VDatePicker
                    v-model="closeForm.payout_date"
                    class="cockpit-day-picker"
                    color="primary"
                    :min="payoutMinDate"
                    hide-header
                    show-adjacent-months
                  />
                </div>
              </div>

              <div class="d-flex justify-end mt-4">
                <BaseButton
                  color="primary"
                  label="Close Month End"
                  prepend-icon="bx-check-circle"
                  @click="openConfirm('close-month')"
                />
              </div>
            </VCardText>
          </template>

          <template v-else>
            <VCardItem>
              <template #prepend>
                <div class="cockpit-action-icon">
                  <VIcon
                    icon="bx-lock-alt"
                    size="22"
                  />
                </div>
              </template>
              <VCardTitle>Month closed</VCardTitle>
              <VCardSubtitle>
                {{ currentMonthClosing.month_label }} is locked for payout processing
              </VCardSubtitle>
            </VCardItem>
            <VDivider />
            <VCardText>
              <VAlert
                type="info"
                variant="tonal"
                class="mb-0"
              >
                <p class="mb-2">
                  Month of
                  <strong>{{ currentMonthClosing.month_label }}</strong>
                  has been closed by
                  <em>{{ currentMonthClosing.closed_by }}</em>
                  on
                  <strong>{{ currentMonthClosing.closing_date }}</strong>.
                </p>
                <p class="mb-0">
                  Monthly payout of owners will be disbursed on
                  <strong>{{ currentMonthClosing.payout_date }}</strong>.
                </p>
              </VAlert>
            </VCardText>
          </template>
        </VCard>

        <div id="month-closing-history">
          <DataTableShell
            title="Month Closing History"
            :empty="filteredHistory.length === 0"
            :empty-colspan="4"
            empty-title="No closing history for this year"
          >
            <template #actions>
              <BaseButton
                variant="tonal"
                size="small"
                label="Property payout history"
                prepend-icon="bx-link-external"
                :to="{ path: '/admin/reports/properties/closing-history' }"
              />
            </template>
            <template #toolbar>
              <div class="cockpit-history-toolbar">
                <BaseSelect
                  v-model="historyYear"
                  label="Year"
                  :items="yearOptions"
                  size="small"
                  hide-details
                  style="max-width: 10rem"
                />
              </div>
            </template>
            <template #head>
              <thead>
                <tr>
                  <th>Closed Month</th>
                  <th>Closing Date</th>
                  <th>Payout Date</th>
                  <th>Closed By</th>
                </tr>
              </thead>
            </template>

            <tr
              v-for="row in filteredHistory"
              :key="row.id"
            >
              <td>{{ row.closed_month }}</td>
              <td>{{ row.closing_date }}</td>
              <td>{{ row.payout_date }}</td>
              <td>{{ row.closed_by }}</td>
            </tr>
          </DataTableShell>
        </div>
      </VCol>

      <!-- Owner actions -->
      <VCol
        cols="12"
        lg="4"
      >
        <VCard class="mb-4">
          <VCardItem>
            <template #prepend>
              <div class="cockpit-action-icon">
                <VIcon
                  icon="bx-file"
                  size="22"
                />
              </div>
            </template>
            <VCardTitle>Owner agreement screen</VCardTitle>
            <VCardSubtitle>
              Re-enable the post-login agreement page for property owners
            </VCardSubtitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <p class="text-body-2 text-medium-emphasis mb-4">
              This resets agreement display so owners see the agreement screen again after login.
            </p>
            <BaseButton
              color="primary"
              block
              label="Enable Agreement Page For Owners"
              prepend-icon="bx-shield-quarter"
              @click="openConfirm('enable-agreement')"
            />
          </VCardText>
        </VCard>

        <VCard>
          <VCardItem>
            <template #prepend>
              <div class="cockpit-action-icon">
                <VIcon
                  icon="bx-envelope"
                  size="22"
                />
              </div>
            </template>
            <VCardTitle>Terms update email</VCardTitle>
            <VCardSubtitle>
              Notify active owners about updated terms of use
            </VCardSubtitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <VAlert
              type="info"
              variant="tonal"
              density="compact"
              class="mb-4"
            >
              By activating these terms, all new active owners who haven't agreed yet will receive an email
              about the update. On login, every owner will see the terms agreement screen configured in
              <NuxtLink
                to="/admin/cms"
                class="cockpit-inline-link"
              >
                Admin CMS
              </NuxtLink>.
            </VAlert>
            <BaseButton
              color="primary"
              block
              label="Send Terms Update Email To Owners"
              prepend-icon="bx-send"
              @click="openConfirm('send-invitations')"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <ConfirmDialog
      v-model="confirmDialog.open"
      :title="confirmTitle"
      :confirm-label="confirmLabel"
      :confirm-color="confirmColor"
      @confirm="runConfirmedAction"
      @cancel="closeConfirm"
    >
      <template v-if="confirmDialog.kind === 'close-month'">
        <p class="mb-3">
          Once confirmed, owners will receive an email that their monthly payout will be disbursed on
          <strong>{{ formatDateValue(closeForm.payout_date) }}</strong>.
        </p>
        <p class="mb-0">
          Are you sure you want to mark
          <strong class="text-primary">{{ selectedMonthName }}</strong>
          as closed?
        </p>
      </template>
      <template v-else-if="confirmDialog.kind === 'enable-agreement'">
        <p class="mb-0">
          This action will enable the agreement screen right after login for all property owners once again.
          Are you sure about this?
        </p>
      </template>
      <template v-else-if="confirmDialog.kind === 'send-invitations'">
        <p class="mb-0">
          Are you sure you wish to send invitations to active owners?
        </p>
      </template>
    </ConfirmDialog>
  </div>
</template>

<style scoped>
.cockpit-action-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

.cockpit-inline-link {
  font-weight: 600;
  color: rgb(var(--v-theme-primary));
  text-decoration: none;
}

.cockpit-inline-link:hover {
  text-decoration: underline;
}

.cockpit-history-toolbar {
  display: flex;
  justify-content: flex-end;
}

.cockpit-calendars {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem;
  align-items: start;
}

.cockpit-calendar-panel {
  min-width: 0;
}

.cockpit-calendar-panel__label {
  margin-bottom: 0.625rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.month-picker {
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
}

.month-picker__year {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.5rem 0.625rem;
  background: rgba(75, 70, 92, 0.06);
}

.month-picker__year-label {
  font-size: 0.9375rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.85);
}

.month-picker__nav {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.75rem;
  height: 1.75rem;
  border: 0;
  border-radius: 0.375rem;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.7);
  cursor: pointer;
}

.month-picker__nav:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
}

.month-picker__grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.5rem;
  padding: 1rem 0.875rem 1.125rem;
}

.month-picker__month {
  min-height: 2.25rem;
  border: 0;
  border-radius: 0.375rem;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.62);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
}

.month-picker__month:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
}

.month-picker__month--selected {
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.month-picker__month--selected:hover {
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.cockpit-day-picker {
  width: 100%;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
  overflow: hidden;
}

.cockpit-day-picker :deep(.v-picker__body),
.cockpit-day-picker :deep(.v-date-picker-month) {
  width: 100%;
}

.cockpit-day-picker :deep(.v-date-picker-controls) {
  padding-inline: 0.5rem;
}

@media (max-width: 959px) {
  .cockpit-calendars {
    grid-template-columns: 1fr;
  }
}
</style>
