<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'email', label: 'Email', type: 'email' }, { key: 'phone', label: 'Phone' }]
</script>

<template><AdminEntityCrud title="Managers" subtitle="Manage staff managers" endpoint="/admin/managers" :fields="fields" /></template>
