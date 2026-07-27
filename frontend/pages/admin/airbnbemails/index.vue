<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Airbnb emails" subtitle="Imported Airbnb email messages" endpoint="/admin/imports/emails" :columns="['id', 'subject', 'from', 'created_at']" /></template>
