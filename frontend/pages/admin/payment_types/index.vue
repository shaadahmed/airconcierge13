<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'description', label: 'Description' }]
</script>

<template><AdminEntityCrud title="Payment types" subtitle="Manage payment categories" endpoint="/admin/payment_types" :fields="fields" /></template>
