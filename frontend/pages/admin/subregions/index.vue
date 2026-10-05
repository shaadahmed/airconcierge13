<script setup>
const route = useRoute()

definePageMeta({ middleware: 'auth' })

await navigateTo({
  path: '/admin/regions',
  query: route.query.region_id ? { region_id: route.query.region_id } : {},
  replace: true,
})
</script>

<template>
  <div />
</template>
