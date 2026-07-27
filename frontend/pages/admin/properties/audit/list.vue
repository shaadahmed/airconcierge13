<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Property audit" subtitle="Property change history" endpoint="/admin/properties/audit/list" :columns="['id', 'property_id', 'action', 'created_at']" /></template>
