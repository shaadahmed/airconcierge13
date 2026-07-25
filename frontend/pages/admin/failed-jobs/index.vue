<script setup>
const failedJobs = useFailedJobsStore()
const rows = computed(() => failedJobs.data || [])

const confirm = reactive({
  open: false,
  action: null,
  uuid: null,
  title: '',
  message: '',
  confirmLabel: 'Confirm',
  confirmColor: 'primary',
})

onMounted(() => failedJobs.load())

const askRetryAll = () => {
  Object.assign(confirm, {
    open: true,
    action: 'retryAll',
    uuid: null,
    title: 'Retry all failed jobs?',
    message: 'All failed jobs will be pushed back onto the queue.',
    confirmLabel: 'Retry all',
    confirmColor: 'primary',
  })
}

const askRetry = uuid => {
  Object.assign(confirm, {
    open: true,
    action: 'retry',
    uuid,
    title: 'Retry this job?',
    message: `Retry failed job ${uuid}.`,
    confirmLabel: 'Retry',
    confirmColor: 'primary',
  })
}

const askForget = uuid => {
  Object.assign(confirm, {
    open: true,
    action: 'forget',
    uuid,
    title: 'Forget this job?',
    message: `Permanently remove failed job ${uuid} from the table.`,
    confirmLabel: 'Forget',
    confirmColor: 'error',
  })
}

const onConfirm = async () => {
  try {
    if (confirm.action === 'retryAll')
      await failedJobs.retryAll()
    else if (confirm.action === 'retry')
      await failedJobs.retry(confirm.uuid)
    else if (confirm.action === 'forget')
      await failedJobs.remove(confirm.uuid)

    confirm.open = false
  }
  catch {
    // Errors surface via the store.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Failed jobs"
      subtitle="Superadmin retry dashboard (no Horizon)"
    >
      <template #actions>
        <BaseButton
          label="Retry all"
          color="primary"
          :loading="failedJobs.loading"
          @click="askRetryAll"
        />
      </template>
    </PageHeader>

    <DataTableShell
      title="Failed queue jobs"
      :loading="failedJobs.loading"
      :errors="failedJobs.errors"
      :empty="rows.length === 0"
      :empty-colspan="6"
      empty-title="No failed jobs"
    >
      <template #head>
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
      </template>

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
            @click="askRetry(job.uuid)"
          />
          <BaseButton
            size="small"
            variant="tonal"
            color="error"
            label="Forget"
            @click="askForget(job.uuid)"
          />
        </td>
      </tr>
    </DataTableShell>

    <ConfirmDialog
      v-model="confirm.open"
      :title="confirm.title"
      :message="confirm.message"
      :confirm-label="confirm.confirmLabel"
      :confirm-color="confirm.confirmColor"
      :loading="failedJobs.loading"
      @confirm="onConfirm"
    />
  </div>
</template>
