<script setup>
const route = useRoute()

// Dedicated edit URL opens the list page with the right-panel edit form.
await navigateTo({
  path: '/admin/dashboard/ownerblock/all',
  query: { edit: String(route.params.id) },
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div />
</template>
