<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Guest email export" subtitle="Download the guest email list as a CSV file" endpoint="/admin/export/guest-emails-list" :columns="['email']" exportable /></template>
