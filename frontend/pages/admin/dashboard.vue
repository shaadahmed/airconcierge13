<script setup>
const dashboard = useDashboardStore()
const stats = computed(() => dashboard.data.stats || {})
const revenue = computed(() => dashboard.data.revenue || { labels: [], values: [] })

const statCards = computed(() => ([
  { key: 'bookings_this_month', title: 'Bookings this month', color: 'primary', icon: 'bx-calendar' },
  { key: 'cancelled_this_month', title: 'Cancelled this month', color: 'error', icon: 'bx-x-circle' },
  { key: 'active_properties', title: 'Active properties', color: 'success', icon: 'bx-building' },
  { key: 'owners', title: 'Owners', color: 'info', icon: 'bx-user' },
  { key: 'revenue_this_month', title: 'Revenue this month', color: 'warning', icon: 'bx-dollar' },
]))

const chartSeries = computed(() => ([
  {
    name: 'Guest paid',
    data: revenue.value.values || [],
  },
]))

const chartOptions = computed(() => ({
  chart: {
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  dataLabels: { enabled: false },
  stroke: {
    width: 3,
    curve: 'smooth',
  },
  colors: ['#696cff'],
  grid: {
    strokeDashArray: 6,
    borderColor: 'rgba(75, 70, 92, 0.12)',
  },
  xaxis: {
    categories: revenue.value.labels || [],
    labels: { style: { colors: '#a5a3ae' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: { colors: '#a5a3ae' },
      formatter: value => Number(value).toLocaleString(),
    },
  },
  tooltip: {
    y: {
      formatter: value => `$${Number(value).toLocaleString()}`,
    },
  },
}))

onMounted(() => dashboard.load())

definePageMeta({ middleware: 'auth', layout: 'default' })
</script>

<template>
  <div>
    <PageHeader
      title="Dashboard"
      subtitle="Operational snapshot and trailing revenue"
    />

    <AppAlert :errors="dashboard.errors" />

    <VRow class="mb-6">
      <VCol
        v-for="card in statCards"
        :key="card.key"
        cols="12"
        sm="6"
        lg="4"
        xl
      >
        <VCard>
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-body-2 text-medium-emphasis mb-1">
                {{ card.title }}
              </div>
              <div class="text-h4">
                {{ stats[card.key] ?? '—' }}
              </div>
            </div>
            <VAvatar
              :color="card.color"
              variant="tonal"
              rounded
              size="42"
            >
              <VIcon :icon="card.icon" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard title="Revenue trend">
      <VCardText>
        <ClientOnly>
          <VueApexCharts
            v-if="(revenue.labels || []).length"
            type="area"
            height="320"
            :options="chartOptions"
            :series="chartSeries"
          />
          <EmptyState
            v-else-if="!dashboard.loading"
            title="No chart data"
            description="Revenue chart will appear once bookings exist."
          />
        </ClientOnly>
      </VCardText>
    </VCard>

    <VProgressLinear
      v-if="dashboard.loading"
      indeterminate
      class="mt-6"
    />

    <VAlert
      type="info"
      variant="tonal"
      class="mt-6"
    >
      Staff commission tables, filtered headline KPIs, and the full legacy performance-chart suite remain follow-up cards (not ported in this pass).
    </VAlert>
  </div>
</template>
