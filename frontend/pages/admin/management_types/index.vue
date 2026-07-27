<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'name', label: 'Name' },
]
</script>

<template>
  <AdminEntityCrud
    title="Management types"
    subtitle="Manage management categories"
    endpoint="/admin/management_types"
    :fields="fields"
  />
</template>
