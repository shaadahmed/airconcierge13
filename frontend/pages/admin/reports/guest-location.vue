<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Guest locations" subtitle="Guest location reporting" endpoint="/admin/reports/guest-location" :columns="['location', 'guests', 'bookings', 'revenue']" /></template>
