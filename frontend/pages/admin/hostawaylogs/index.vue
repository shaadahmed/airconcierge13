<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Hostaway logs" subtitle="Hostaway integration activity" endpoint="/admin/hostawaylogs" :columns="['id', 'event', 'status', 'created_at']" /></template>
