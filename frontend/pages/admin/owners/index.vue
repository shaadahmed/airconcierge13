<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'first_name', label: 'First name' },
  { key: 'last_name', label: 'Last name' },
  { key: 'full_name', label: 'Full name' },
  { key: 'owner_email', label: 'Email', type: 'email' },
  { key: 'owner_phone', label: 'Phone' },
  { key: 'region_id', label: 'Region ID', type: 'number' },
  { key: 'payment_method', label: 'Payment method' },
  { key: 'owner_payout_information', label: 'Payout information', type: 'textarea' },
  { key: 'w9_on_file', label: 'W9 on file', type: 'checkbox', default: false },
]

const columns = [
  { key: 'full_name', label: 'Name' },
  { key: 'owner_email', label: 'Email' },
  { key: 'owner_phone', label: 'Phone' },
  { key: 'region_id', label: 'Region' },
  { key: 'w9_on_file', label: 'W9', type: 'checkbox' },
]
</script>

<template>
  <AdminEntityCrud
    title="Owners"
    subtitle="Manage property owners"
    endpoint="/admin/owners"
    :fields="fields"
    :columns="columns"
  />
</template>
