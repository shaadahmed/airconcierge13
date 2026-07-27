<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email', type: 'email' },
  { key: 'password', label: 'Password', type: 'password' },
  { key: 'active', label: 'Active', type: 'checkbox', default: true },
]

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'active', label: 'Active', type: 'checkbox' },
]
</script>

<template>
  <AdminEntityCrud
    title="Vendors"
    subtitle="Manage cleaner / vendor accounts"
    endpoint="/admin/vendors"
    :fields="fields"
    :columns="columns"
  />
</template>
