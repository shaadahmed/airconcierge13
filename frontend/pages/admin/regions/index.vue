<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'region_name', label: 'Region name' },
  { key: 'shortcode', label: 'Shortcode' },
  { key: 'color', label: 'Color' },
]

const columns = [
  { key: 'region_name', label: 'Region name' },
  { key: 'shortcode', label: 'Shortcode' },
  { key: 'color', label: 'Color' },
]
</script>

<template>
  <AdminEntityCrud
    title="Regions"
    subtitle="Manage property regions"
    endpoint="/admin/regions"
    :fields="fields"
    :columns="columns"
  />
</template>
