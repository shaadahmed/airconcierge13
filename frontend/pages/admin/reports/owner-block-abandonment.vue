<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Owner block abandonment" subtitle="Abandoned owner-block requests" endpoint="/admin/reports/owner-block-abandonment" :columns="['property_title', 'owner', 'created_at', 'status']" /></template>
