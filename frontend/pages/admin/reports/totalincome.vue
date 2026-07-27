<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Total income" subtitle="Total income reporting" endpoint="/admin/reports/totalincome" :columns="['period', 'income', 'expenses', 'net_income']" /></template>
