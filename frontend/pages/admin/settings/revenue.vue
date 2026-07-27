<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'property_id', label: 'Property ID', type: 'number' },
  { key: 'dynamic_pricing', label: 'Dynamic pricing', type: 'number', default: 0 },
  { key: 'base_rate', label: 'Base rate', type: 'number' },
  { key: 'min_rate', label: 'Min rate', type: 'number' },
]

const columns = [
  { key: 'property_id', label: 'Property ID' },
  { key: 'dynamic_pricing', label: 'Dynamic pricing' },
  { key: 'base_rate', label: 'Base rate' },
  { key: 'min_rate', label: 'Min rate' },
]
</script>

<template>
  <AdminEntityCrud
    title="Revenue settings"
    subtitle="Per-property revenue configuration"
    endpoint="/admin/settings/revenue"
    :fields="fields"
    :columns="columns"
  />
</template>
