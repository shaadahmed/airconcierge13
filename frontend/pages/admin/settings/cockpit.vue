<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Cockpit settings" subtitle="Cockpit configuration" endpoint="/admin/settings/cockpit" :columns="['name', 'value', 'updated_at']" /></template>
