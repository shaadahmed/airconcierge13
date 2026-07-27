<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Property reports" subtitle="Property performance reporting" endpoint="/admin/reports/properties" :columns="['property_title', 'bookings', 'revenue', 'occupancy']" /></template>
