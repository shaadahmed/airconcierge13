<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Booking stays" subtitle="Booking stay reporting" endpoint="/admin/reports/bookingstay" :columns="['booking_id', 'guest', 'check_in', 'check_out']" /></template>
