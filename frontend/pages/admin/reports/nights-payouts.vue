<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Nights payouts" subtitle="Night and payout reporting" endpoint="/admin/reports/nights-payouts" :columns="['property_title', 'nights', 'payout', 'period']" /></template>
