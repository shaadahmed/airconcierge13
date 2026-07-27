<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'platform_name', label: 'Platform name' },
]

const columns = [
  { key: 'platform_name', label: 'Platform name' },
]
</script>

<template>
  <AdminEntityCrud
    title="Platforms"
    subtitle="Manage booking platforms"
    endpoint="/admin/platforms"
    :fields="fields"
    :columns="columns"
  />
</template>
