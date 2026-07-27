<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="System settings" subtitle="System configuration" endpoint="/admin/settings/system" :columns="['name', 'value', 'updated_at']" /></template>
