<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'name', label: 'Name' },
]

const columns = [
  { key: 'name', label: 'Name' },
]
</script>

<template>
  <AdminEntityCrud
    title="Payment types"
    subtitle="Manage payment categories"
    endpoint="/admin/payment_types"
    :fields="fields"
    :columns="columns"
  />
</template>
