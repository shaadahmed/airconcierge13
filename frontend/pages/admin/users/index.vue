<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email', type: 'email' },
  { key: 'password', label: 'Password', type: 'password' },
  { key: 'role', label: 'Role' },
  { key: 'active', label: 'Active', type: 'checkbox', default: true },
]

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' },
  { key: 'active', label: 'Active', type: 'checkbox' },
]
</script>

<template>
  <AdminEntityCrud
    title="Users"
    subtitle="Manage administrator accounts"
    endpoint="/admin/users"
    :fields="fields"
    :columns="columns"
  />
</template>
