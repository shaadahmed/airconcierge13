<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'email', label: 'Email', type: 'email' }, { key: 'phone', label: 'Phone' }]
</script>

<template><AdminEntityCrud title="Vendors" subtitle="Manage service vendors" endpoint="/admin/vendors" :fields="fields" /></template>
