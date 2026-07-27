<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Closing history" subtitle="Property closing history" endpoint="/admin/reports/properties/closing-history" :columns="['property_title', 'closed_at', 'reason', 'status']" /></template>
