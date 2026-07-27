<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'name', label: 'Name' }, { key: 'email', label: 'Email', type: 'email' }, { key: 'role', label: 'Role' }, { key: 'active', label: 'Active', type: 'number', default: 1 }]
</script>

<template><AdminEntityCrud title="Users" subtitle="Manage administrator accounts" endpoint="/admin/users" :fields="fields" /></template>
