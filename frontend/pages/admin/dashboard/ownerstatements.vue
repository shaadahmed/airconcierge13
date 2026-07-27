<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template><AdminEndpointTable title="Owner statements" subtitle="Owner statement dashboard" endpoint="/admin/dashboard/ownerstatements" :columns="['owner', 'property', 'period', 'owner_payout']" /></template>
