<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Regional income" subtitle="Income grouped by region" endpoint="/admin/reports/regionsincome" :columns="['region', 'income', 'expenses', 'net_income']" /></template>
