<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Owner blocks" subtitle="All owner-block requests" endpoint="/admin/dashboard/ownerblock/all" :columns="['id', 'property_id', 'start_date', 'end_date']" /></template>
