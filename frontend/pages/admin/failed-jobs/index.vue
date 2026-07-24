<script setup>
const failedJobs = useFailedJobsStore()
const rows = computed(() => failedJobs.data || [])

onMounted(() => failedJobs.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <VCard title="Failed jobs">
    <VCardText class="d-flex justify-space-between align-center flex-wrap gap-4">
      <p class="mb-0 text-body-1">
        Superadmin retry dashboard (no Horizon).
      </p>
      <BaseButton
        label="Retry all"
        color="primary"
        :loading="failedJobs.loading"
        @click="failedJobs.retryAll()"
      />
    </VCardText>

    <VAlert
      v-if="failedJobs.errors.general"
      type="error"
      class="mx-4 mb-4"
    >
      {{ failedJobs.errors.general[0] }}
    </VAlert>

    <VTable>
      <thead>
        <tr>
          <th>UUID</th>
          <th>Job</th>
          <th>Queue</th>
          <th>Failed at</th>
          <th>Exception</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="job in rows"
          :key="job.uuid"
        >
          <td>{{ job.uuid }}</td>
          <td>{{ job.display_name }}</td>
          <td>{{ job.queue }}</td>
          <td>{{ job.failed_at }}</td>
          <td>
            <pre class="text-caption">{{ job.exception }}</pre>
          </td>
          <td class="text-no-wrap">
            <BaseButton
              size="small"
              variant="tonal"
              label="Retry"
              class="me-2"
              @click="failedJobs.retry(job.uuid)"
            />
            <BaseButton
              size="small"
              variant="tonal"
              color="error"
              label="Forget"
              @click="failedJobs.remove(job.uuid)"
            />
          </td>
        </tr>
        <tr v-if="!failedJobs.loading && rows.length === 0">
          <td colspan="6">
            No failed jobs.
          </td>
        </tr>
      </tbody>
    </VTable>

    <VProgressLinear
      v-if="failedJobs.loading"
      indeterminate
    />
  </VCard>
</template>
