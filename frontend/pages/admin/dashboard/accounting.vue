<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Accounting" subtitle="Accounting dashboard summary" endpoint="/admin/dashboard/accounting" :columns="['name', 'amount', 'period']" /></template>
