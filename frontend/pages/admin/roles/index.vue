<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Roles" subtitle="Available application roles" endpoint="/admin/roles" :columns="['id', 'name', 'description']" /></template>
