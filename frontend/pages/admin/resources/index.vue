<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'parent_id', label: 'Parent ID', type: 'number', default: 0 },
  { key: 'name', label: 'Name' },
  { key: 'path', label: 'Path' },
  { key: 'description', label: 'Description' },
  { key: 'link', label: 'Clickable link', type: 'checkbox', default: true },
  { key: 'external_link', label: 'External link', type: 'checkbox', default: false },
  { key: 'order', label: 'Order', type: 'number', default: 0 },
  { key: 'icon_class', label: 'Icon class' },
]

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'parent_id', label: 'Parent' },
  { key: 'name', label: 'Name' },
  { key: 'path', label: 'Path' },
  { key: 'order', label: 'Order' },
]
</script>

<template>
  <AdminEntityCrud
    title="System Resources"
    subtitle="Manage sidebar resources"
    endpoint="/admin/resources"
    :fields="fields"
    :columns="columns"
  />
</template>
