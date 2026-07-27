<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Property thresholds" subtitle="Threshold performance report" endpoint="/admin/reports/properties/threshold" :columns="['property_title', 'threshold', 'value', 'status']" /></template>
