<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'email', label: 'Email', type: 'email' }, { key: 'phone', label: 'Phone' }]
</script>

<template><AdminEntityCrud title="Owners" subtitle="Manage property owners" endpoint="/admin/owners" :fields="fields" /></template>
