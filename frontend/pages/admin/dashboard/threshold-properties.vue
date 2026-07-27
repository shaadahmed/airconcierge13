<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Threshold properties" subtitle="Properties approaching configured thresholds" endpoint="/admin/dashboard/threshold-properties" :columns="['id', 'property_title', 'threshold', 'value']" /></template>
