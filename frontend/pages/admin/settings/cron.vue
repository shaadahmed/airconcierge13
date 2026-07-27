<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Cron settings" subtitle="Scheduled task configuration" endpoint="/admin/settings/cron" :columns="['name', 'schedule', 'last_run_at']" /></template>
