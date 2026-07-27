<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'url', label: 'URL' }, { key: 'description', label: 'Description' }]
</script>

<template><AdminEntityCrud title="Resources" subtitle="Manage shared resources" endpoint="/admin/resources" :fields="fields" /></template>
