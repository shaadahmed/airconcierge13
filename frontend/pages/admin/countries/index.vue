<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'code', label: 'Code' }]
</script>

<template><AdminEntityCrud title="Countries" subtitle="Manage countries" endpoint="/admin/countries" :fields="fields" /></template>
