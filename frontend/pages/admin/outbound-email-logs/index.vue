<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Outbound email logs" subtitle="Sent email activity" endpoint="/admin/outbound-email-logs" :columns="['id', 'recipient', 'subject', 'created_at']" /></template>
