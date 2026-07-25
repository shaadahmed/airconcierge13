<script setup>
import { ownerStatementService } from '@/services/ownerStatementService'

const statements = useOwnerStatementsStore()

const filters = reactive({
  property_id: '',
  date_range: 'this_month',
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
})

const properties = computed(() => statements.meta?.properties || [])
const report = computed(() => statements.report)

const moneyFields = [
  'accomodations',
  'cleaning_fee',
  'tot_charged_to_guest',
  'total_guest_paid',
  'management_fee',
  'owner_payout',
  'booking_payments',
  'property_payments',
  'total_income',
  'total_expenses',
  'net_income',
]

onMounted(async () => {
  await statements.loadMeta()
  if (properties.value[0])
    filters.property_id = properties.value[0].id
})

const runReport = async () => {
  try {
    await statements.loadReport({ ...filters })
  }
  catch {
    // Store exposes errors.
  }
}

const exportCsv = () => {
  window.location.href = ownerStatementService.exportUrl({ ...filters })
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Owner statements"
      subtitle="Profit and loss summary for a property and date range"
    >
      <template #actions>
        <BaseButton
          v-if="report"
          label="Export CSV"
          variant="tonal"
          @click="exportCsv"
        />
      </template>
    </PageHeader>

    <VCard
      title="Filters"
      class="mb-6"
    >
      <VCardText>
        <AppAlert :errors="statements.errors" />
        <VForm @submit.prevent="runReport">
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <BaseSelect
                v-model="filters.property_id"
                label="Property"
                :items="properties.map(p => ({ title: p.property_title, value: p.id }))"
                :error="statements.errors.property_id"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <BaseSelect
                v-model="filters.date_range"
                label="Date range"
                :items="(statements.meta?.date_ranges || []).map(value => ({ title: value.replaceAll('_', ' '), value }))"
                :error="statements.errors.date_range"
              />
            </VCol>
            <VCol
              v-if="filters.date_range === 'specific_month'"
              cols="6"
              md="2"
            >
              <BaseInput
                v-model="filters.month"
                label="Month"
                type="number"
                :error="statements.errors.month"
              />
            </VCol>
            <VCol
              v-if="filters.date_range === 'specific_month'"
              cols="6"
              md="2"
            >
              <BaseInput
                v-model="filters.year"
                label="Year"
                type="number"
                :error="statements.errors.year"
              />
            </VCol>
          </VRow>
          <BaseButton
            type="submit"
            label="Run statement"
            :loading="statements.loading"
          />
        </VForm>
      </VCardText>
    </VCard>

    <VRow v-if="report">
      <VCol
        cols="12"
        md="4"
      >
        <VCard title="Occupancy">
          <VCardText>
            <p class="mb-1">
              Reservations: {{ report.total_reservations }}
            </p>
            <p class="mb-1">
              Booked nights: {{ report.booked_nights }}
            </p>
            <p class="mb-1">
              Blocked nights: {{ report.blocked_nights }}
            </p>
            <p class="mb-1">
              Occupancy: {{ report.occupancy_percentage }}%
            </p>
            <p class="mb-0">
              ADR: {{ report.average_daily_rate }}
            </p>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="Profit and loss"
          :empty="false"
          :empty-colspan="2"
        >
          <template #head>
            <thead>
              <tr>
                <th>Line</th>
                <th>Amount</th>
              </tr>
            </thead>
          </template>
          <tr
            v-for="field in moneyFields"
            :key="field"
          >
            <td class="text-capitalize">
              {{ field.replaceAll('_', ' ') }}
            </td>
            <td>{{ report[field] }}</td>
          </tr>
        </DataTableShell>
      </VCol>
      <VCol
        v-if="report.owner_notes?.length"
        cols="12"
      >
        <VCard title="Owner notes">
          <VList>
            <VListItem
              v-for="(note, index) in report.owner_notes"
              :key="index"
              :title="`${note.guest} (${note.start} – ${note.end})`"
              :subtitle="note.comment"
            />
          </VList>
        </VCard>
      </VCol>
      <VCol
        v-if="report.schema_gaps?.length"
        cols="12"
      >
        <VAlert
          type="info"
          variant="tonal"
        >
          Full legacy line items not yet on L13 bookings schema:
          {{ report.schema_gaps.join(', ') }}. Tracked as a follow-up card.
        </VAlert>
      </VCol>
    </VRow>

    <EmptyState
      v-else-if="!statements.loading"
      title="No statement loaded"
      description="Choose a property and date range, then run the statement."
    />
  </div>
</template>
