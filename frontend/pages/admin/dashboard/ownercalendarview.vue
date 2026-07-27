<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Owner calendar" subtitle="Owner property calendar view" endpoint="/admin/dashboard/ownercalendarview" :columns="['property_id', 'date', 'status', 'owner']" /></template>
