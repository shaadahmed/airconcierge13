<script setup>
/* eslint-disable vue/no-v-html -- descriptions are trusted static fixtures from legacy cron-settings */
import {
  CRON_SETTINGS_TABS,
  MOCK_CRON_JOBS,
  MOCK_DATABASE_RULES,
} from '@/mocks/cronSettings'

definePageMeta({ middleware: 'auth' })

const route = useRoute()
const router = useRouter()

const usingMocks = true
const activeTab = ref('cron-jobs')
const selectedCronId = ref(MOCK_CRON_JOBS[0]?.id ?? null)
const selectedRuleId = ref(MOCK_DATABASE_RULES[0]?.id ?? null)

const tabFromQuery = value => {
  const match = CRON_SETTINGS_TABS.find(tab => tab.value === value)

  return match ? match.value : 'cron-jobs'
}

watch(
  () => route.query.tab,
  value => {
    activeTab.value = tabFromQuery(value)
  },
  { immediate: true },
)

watch(activeTab, value => {
  if (route.query.tab === value)
    return

  router.replace({
    path: route.path,
    query: { ...route.query, tab: value },
  })
})

const selectedCron = computed(() => (
  MOCK_CRON_JOBS.find(job => String(job.id) === String(selectedCronId.value)) || null
))

const selectedRule = computed(() => (
  MOCK_DATABASE_RULES.find(rule => String(rule.id) === String(selectedRuleId.value)) || null
))

const selectCron = id => {
  selectedCronId.value = id
}

const selectRule = id => {
  selectedRuleId.value = id
}

const ruleTypeColor = type => (type === 'automatic' ? 'success' : 'primary')
const ruleTypeLabel = type => (type === 'automatic' ? 'Automatic' : 'Manual')
const ruleTypeHint = type => (
  type === 'automatic'
    ? 'Handled by the system, no manual action required'
    : 'Manual action required, system does not automatically implement this rule'
)

const cellClass = symbol => {
  if (symbol === '✓')
    return 'cron-matrix-cell--ok'
  if (symbol === '✗')
    return 'cron-matrix-cell--no'
  if (symbol === '⛔')
    return 'cron-matrix-cell--deny'

  return ''
}
</script>

<template>
  <div class="cron-page">
    <PageHeader
      class="flex-shrink-0"
      title="Cron Jobs & Database Rules"
      subtitle="Scheduled jobs and system business rules reference"
    />

    <VAlert
      v-if="usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4 cron-page__alert"
    >
      Showing UI preview data until the cron settings API is connected.
    </VAlert>

    <VCard class="cron-page__shell">
      <VTabs
        v-model="activeTab"
        show-arrows
      >
        <VTab
          v-for="tab in CRON_SETTINGS_TABS"
          :key="tab.value"
          :value="tab.value"
        >
          <VIcon
            :icon="tab.icon"
            start
            size="18"
          />
          {{ tab.title }}
        </VTab>
      </VTabs>

      <VDivider />

      <VTabsWindow
        v-model="activeTab"
        class="cron-page__window"
      >
        <!-- Cron Jobs: 40 / 60 -->
        <VTabsWindowItem
          value="cron-jobs"
          class="cron-page__window-item"
        >
          <div class="cron-page__stage cron-page__stage--cron">
            <div class="cron-page__col cron-page__col--list">
              <div class="cron-list">
                <button
                  v-for="job in MOCK_CRON_JOBS"
                  :key="job.id"
                  type="button"
                  class="cron-list-item"
                  :class="{ 'cron-list-item--selected': String(selectedCronId) === String(job.id) }"
                  @click="selectCron(job.id)"
                >
                  <div class="cron-list-item__icon">
                    <VIcon
                      :icon="job.icon"
                      size="22"
                    />
                  </div>
                  <div class="cron-list-item__main">
                    <div class="cron-list-item__title">
                      {{ job.title }}
                    </div>
                    <div class="cron-list-item__subtitle">
                      {{ job.subtitle }}
                    </div>
                  </div>
                  <VIcon
                    icon="bx-chevron-right"
                    size="20"
                    class="cron-list-item__chevron"
                  />
                </button>
              </div>
            </div>

            <div class="cron-page__col cron-page__col--detail">
              <VCard
                v-if="selectedCron"
                class="cron-detail-card"
                variant="outlined"
              >
                <VCardItem class="flex-shrink-0">
                  <template #prepend>
                    <div class="cron-detail-card__icon">
                      <VIcon
                        :icon="selectedCron.icon"
                        size="24"
                      />
                    </div>
                  </template>
                  <VCardTitle>{{ selectedCron.title }}</VCardTitle>
                  <VCardSubtitle>{{ selectedCron.subtitle }}</VCardSubtitle>
                  <template #append>
                    <VBtnToggle
                      density="compact"
                      variant="outlined"
                      divided
                      readonly
                      :model-value="selectedCron.is_active ? 'on' : 'off'"
                    >
                      <VBtn
                        value="on"
                        size="small"
                        color="success"
                        title="This cron functionality is ON"
                      >
                        ON
                      </VBtn>
                      <VBtn
                        value="off"
                        size="small"
                        color="error"
                        disabled
                        title="You cannot turn off this cron job"
                      >
                        OFF
                      </VBtn>
                    </VBtnToggle>
                  </template>
                </VCardItem>

                <VDivider />

                <VCardText class="cron-detail-body">
                  <h3 class="cron-detail-body__heading">
                    How it works
                  </h3>
                  <div
                    class="cron-rich-text"
                    v-html="selectedCron.description"
                  />
                </VCardText>
              </VCard>

              <EmptyState
                v-else
                title="Select a cron job"
                description="Choose a scheduled job from the list to view how it works."
              />
            </div>
          </div>
        </VTabsWindowItem>

        <!-- Database Rules: 60 / 40 -->
        <VTabsWindowItem
          value="database-rules"
          class="cron-page__window-item"
        >
          <div class="cron-page__stage cron-page__stage--rules">
            <div class="cron-page__col cron-page__col--list">
              <div class="rule-card-grid">
                <button
                  v-for="rule in MOCK_DATABASE_RULES"
                  :key="rule.id"
                  type="button"
                  class="rule-card"
                  :class="{ 'rule-card--selected': String(selectedRuleId) === String(rule.id) }"
                  @click="selectRule(rule.id)"
                >
                  <div class="rule-card__top">
                    <span class="rule-card__title">{{ rule.title }}</span>
                    <VChip
                      size="x-small"
                      label
                      :color="ruleTypeColor(rule.type)"
                      :title="ruleTypeHint(rule.type)"
                    >
                      {{ ruleTypeLabel(rule.type) }}
                    </VChip>
                  </div>
                  <p class="rule-card__summary">
                    {{ rule.summary }}
                  </p>
                </button>
              </div>
            </div>

            <div class="cron-page__col cron-page__col--detail">
              <VCard
                v-if="selectedRule"
                class="cron-detail-card"
                variant="outlined"
              >
                <VCardItem class="flex-shrink-0">
                  <VCardTitle>{{ selectedRule.title }}</VCardTitle>
                  <VCardSubtitle>{{ selectedRule.summary }}</VCardSubtitle>
                  <template #append>
                    <VChip
                      size="small"
                      label
                      :color="ruleTypeColor(selectedRule.type)"
                      :title="ruleTypeHint(selectedRule.type)"
                    >
                      {{ ruleTypeLabel(selectedRule.type) }}
                    </VChip>
                  </template>
                </VCardItem>

                <VDivider />

                <VCardText class="cron-detail-body">
                  <div
                    class="cron-rich-text"
                    v-html="selectedRule.descriptionHtml"
                  />

                  <template v-if="selectedRule.matrices?.length">
                    <div
                      v-for="matrix in selectedRule.matrices"
                      :key="matrix.title"
                      class="cron-matrix"
                    >
                      <h4 class="cron-matrix__title">
                        {{ matrix.title }}
                      </h4>
                      <div class="cron-matrix__table-wrap">
                        <table class="cron-matrix__table">
                          <thead>
                            <tr>
                              <th
                                v-for="column in matrix.columns"
                                :key="column"
                              >
                                {{ column }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="row in matrix.rows"
                              :key="row.case"
                            >
                              <td>{{ row.case }}</td>
                              <td
                                v-for="(cell, index) in row.cells"
                                :key="`${row.case}-${index}`"
                                class="cron-matrix-cell"
                                :class="cellClass(cell.symbol)"
                                :title="cell.note || undefined"
                              >
                                <span>{{ cell.symbol }}</span>
                                <VIcon
                                  v-if="cell.note"
                                  icon="bx-info-circle"
                                  size="14"
                                  class="ms-1"
                                  color="primary"
                                />
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </template>

                  <div
                    v-if="selectedRule.pages?.length"
                    class="cron-detail-pages"
                  >
                    <h4 class="cron-detail-pages__heading">
                      Impacted pages
                    </h4>
                    <div class="d-flex flex-column gap-2">
                      <NuxtLink
                        v-for="page in selectedRule.pages"
                        :key="page.to + page.label"
                        :to="page.to"
                        class="cron-detail-pages__link"
                      >
                        <VIcon
                          icon="bx-link-external"
                          size="16"
                        />
                        {{ page.label }}
                      </NuxtLink>
                    </div>
                  </div>
                </VCardText>
              </VCard>

              <EmptyState
                v-else
                title="Select a database rule"
                description="Choose a rule card to view its description and impacted pages."
              />
            </div>
          </div>
        </VTabsWindowItem>
      </VTabsWindow>
    </VCard>
  </div>
</template>

<style scoped>
.cron-page {
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.cron-page__alert {
  flex: 0 0 auto;
}

.cron-page__shell {
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  min-height: 0;
  overflow: hidden;
}

.cron-page__window {
  flex: 1 1 auto;
  min-height: 0;
}

.cron-page__window :deep(.v-window__container),
.cron-page__window-item {
  height: 100%;
}

.cron-page__stage {
  display: grid;
  gap: 1rem;
  align-items: stretch;
  min-height: 0;
  width: 100%;
  padding: 1rem;
  height: 100%;
}

.cron-page__stage--cron {
  grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
}

.cron-page__stage--rules {
  grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
}

.cron-page__col {
  min-width: 0;
  min-height: 0;
  display: flex;
  flex-direction: column;
}

@media (min-width: 960px) {
  .cron-page {
    height: calc(100dvh - 64px - 3rem);
    overflow: hidden;
  }

  .cron-page__shell,
  .cron-page__window,
  .cron-page__col,
  .cron-detail-card {
    height: 100%;
    max-height: 100%;
  }
}

.cron-list {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding-right: 0.25rem;
}

.cron-list-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
  background: rgb(var(--v-theme-surface));
  text-align: left;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.cron-list-item:hover {
  border-color: rgba(var(--v-theme-primary), 0.35);
  background: rgba(var(--v-theme-primary), 0.03);
}

.cron-list-item--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.06);
}

.cron-list-item__icon {
  flex-shrink: 0;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
}

.cron-list-item__main {
  flex: 1 1 auto;
  min-width: 0;
}

.cron-list-item__title {
  font-weight: 600;
  font-size: 0.9375rem;
  line-height: 1.3;
}

.cron-list-item__subtitle {
  margin-top: 0.2rem;
  font-size: 0.8125rem;
  line-height: 1.4;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.cron-list-item__chevron {
  flex-shrink: 0;
  margin-top: 0.15rem;
  color: rgba(var(--v-theme-on-surface), 0.35);
}

.rule-card-grid {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
  align-content: start;
  padding-right: 0.25rem;
}

.rule-card {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 100%;
  padding: 1rem;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
  background: rgb(var(--v-theme-surface));
  text-align: left;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}

.rule-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.35);
  background: rgba(var(--v-theme-primary), 0.03);
}

.rule-card--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.06);
  box-shadow: 0 6px 18px rgba(var(--v-theme-primary), 0.08);
}

.rule-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
}

.rule-card__title {
  font-weight: 600;
  font-size: 0.9rem;
  line-height: 1.35;
}

.rule-card__summary {
  margin: 0;
  font-size: 0.8125rem;
  line-height: 1.45;
  color: rgba(var(--v-theme-on-surface), 0.62);
}

.cron-detail-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  width: 100%;
}

.cron-detail-card__icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

.cron-detail-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
}

.cron-detail-body__heading {
  margin: 0 0 0.75rem;
  font-size: 1.05rem;
  font-weight: 700;
}

.cron-rich-text :deep(p) {
  margin: 0 0 0.75rem;
  font-size: 0.9375rem;
  line-height: 1.55;
}

.cron-rich-text :deep(h4) {
  margin: 1rem 0 0.5rem;
  font-size: 0.95rem;
  font-weight: 700;
}

.cron-rich-text :deep(ul),
.cron-rich-text :deep(ol) {
  margin: 0 0 0.75rem;
  padding-left: 1.25rem;
  font-size: 0.9375rem;
  line-height: 1.55;
}

.cron-rich-text :deep(li + li) {
  margin-top: 0.35rem;
}

.cron-detail-pages {
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(75, 70, 92, 0.1);
}

.cron-detail-pages__heading {
  margin: 0 0 0.5rem;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.cron-detail-pages__link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(var(--v-theme-primary));
  text-decoration: none;
}

.cron-detail-pages__link:hover {
  text-decoration: underline;
}

.cron-matrix {
  margin-top: 1.25rem;
}

.cron-matrix__title {
  margin: 0 0 0.5rem;
  font-size: 0.9rem;
  font-weight: 700;
}

.cron-matrix__table-wrap {
  overflow-x: auto;
  border: 1px solid rgba(75, 70, 92, 0.12);
  border-radius: 0.5rem;
}

.cron-matrix__table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8125rem;
}

.cron-matrix__table th,
.cron-matrix__table td {
  padding: 0.625rem 0.75rem;
  border-bottom: 1px solid rgba(75, 70, 92, 0.1);
  text-align: left;
  vertical-align: middle;
}

.cron-matrix__table th {
  background: rgba(75, 70, 92, 0.04);
  font-weight: 700;
}

.cron-matrix__table tr:last-child td {
  border-bottom: 0;
}

.cron-matrix-cell {
  text-align: center !important;
  font-weight: 700;
  white-space: nowrap;
}

.cron-matrix-cell--ok {
  color: rgb(var(--v-theme-success));
}

.cron-matrix-cell--no {
  color: rgb(var(--v-theme-error));
}

.cron-matrix-cell--deny {
  color: rgb(var(--v-theme-error));
}

@media (max-width: 959px) {
  .cron-page__stage--cron,
  .cron-page__stage--rules {
    grid-template-columns: 1fr;
  }

  .cron-list,
  .rule-card-grid {
    max-height: 22rem;
  }

  .rule-card-grid {
    grid-template-columns: 1fr;
  }
}
</style>
