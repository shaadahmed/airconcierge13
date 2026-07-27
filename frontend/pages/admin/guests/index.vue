<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'guest_name', label: 'Guest name' },
  { key: 'first_name', label: 'First name' },
  { key: 'last_name', label: 'Last name' },
  { key: 'email', label: 'Email', type: 'email' },
  { key: 'phone', label: 'Phone' },
  { key: 'city', label: 'City' },
  { key: 'state', label: 'State' },
  { key: 'country', label: 'Country' },
  { key: 'notes', label: 'Notes', type: 'textarea' },
  { key: 'blacklisted', label: 'Blacklisted', type: 'checkbox', default: false },
]

const columns = [
  { key: 'guest_name', label: 'Guest name' },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Phone' },
  { key: 'city', label: 'City' },
  { key: 'country', label: 'Country' },
  { key: 'blacklisted', label: 'Blacklisted', type: 'checkbox' },
]
</script>

<template>
  <AdminEntityCrud
    title="Guests"
    subtitle="Manage guest records"
    endpoint="/admin/guests"
    :fields="fields"
    :columns="columns"
  />
</template>
