<script setup>
// Dedicated create URL opens the list page with the right-panel create form.
await navigateTo({
  path: '/admin/dashboard/ownerblock/all',
  query: { create: '1' },
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div />
</template>
