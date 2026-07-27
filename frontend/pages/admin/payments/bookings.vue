<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Costs & Expenses" subtitle="Booking payment and expense records" endpoint="/admin/payments" :columns="['id', 'property_id', 'amount', 'created_at']" /></template>
