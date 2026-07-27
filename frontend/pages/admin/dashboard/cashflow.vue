<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Cash flow" subtitle="Cash flow dashboard" endpoint="/admin/dashboard/cashflow" :columns="['period', 'income', 'expenses', 'net_income']" /></template>
