<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'value', label: 'Value' }, { key: 'description', label: 'Description' }]
</script>

<template><AdminEntityCrud title="Revenue settings" subtitle="Manage revenue configuration" endpoint="/admin/settings/revenue" :fields="fields" /></template>
