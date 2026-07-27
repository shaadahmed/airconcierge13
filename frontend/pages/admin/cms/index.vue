<script setup>
definePageMeta({ middleware: 'auth' })
const fields = [{ key: 'title', label: 'Title' }, { key: 'slug', label: 'Slug' }, { key: 'content', label: 'Content' }]
</script>

<template><AdminEntityCrud title="CMS" subtitle="Manage content pages" endpoint="/admin/cms" :fields="fields" /></template>
