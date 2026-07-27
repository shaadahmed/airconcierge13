<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'country_id', label: 'Country ID', type: 'number' }]
</script>

<template><AdminEntityCrud title="Regions" subtitle="Manage property regions" endpoint="/admin/regions" :fields="fields" /></template>
