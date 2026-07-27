<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'code', label: 'Code' }]
</script>

<template><AdminEntityCrud title="Platforms" subtitle="Manage booking platforms" endpoint="/admin/platforms" :fields="fields" /></template>
