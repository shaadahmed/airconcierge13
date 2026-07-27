<script setup>
definePageMeta({ middleware: 'auth' })

const fields = [
  { key: 'first_name', label: 'First name' },
  { key: 'last_name', label: 'Last name' },
  { key: 'email', label: 'Email', type: 'email' },
  { key: 'phone', label: 'Phone' },
  { key: 'regional_manager', label: 'Regional manager', type: 'checkbox', default: true },
  { key: 'is_active', label: 'Active', type: 'checkbox', default: true },
  { key: 'salary_type', label: 'Salary type', type: 'number' },
  { key: 'employment_status', label: 'Employment status', type: 'number' },
  { key: 'home_address', label: 'Home address' },
  { key: 'compensation_structure', label: 'Compensation structure', type: 'textarea' },
  { key: 'city', label: 'City' },
  { key: 'state', label: 'State' },
  { key: 'zip', label: 'Zip' },
]

const columns = [
  { key: 'first_name', label: 'First name' },
  { key: 'last_name', label: 'Last name' },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Phone' },
  { key: 'regional_manager', label: 'Regional', type: 'checkbox' },
  { key: 'is_active', label: 'Active', type: 'checkbox' },
]
</script>

<template>
  <AdminEntityCrud
    title="Regional Managers"
    subtitle="Manage regional managers"
    endpoint="/admin/managers"
    :fields="fields"
    :columns="columns"
  />
</template>
