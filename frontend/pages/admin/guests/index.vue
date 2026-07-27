<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'email', label: 'Email', type: 'email' }, { key: 'phone', label: 'Phone' }]
</script>

<template><AdminEntityCrud title="Guests" subtitle="Manage guest records" endpoint="/admin/guests" :fields="fields" /></template>
