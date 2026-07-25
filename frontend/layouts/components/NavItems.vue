<script setup>
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'

const items = [
  { title: 'Dashboard', icon: 'bx-home', to: '/admin/dashboard' },
  { title: 'Bookings', icon: 'bx-calendar', to: '/admin/bookings' },
  { title: 'Properties', icon: 'bx-building', to: '/admin/properties' },
  { title: 'Payments', icon: 'bx-credit-card', to: '/admin/payments' },
  { title: 'Property Payments', icon: 'bx-building-house', to: '/admin/property-payments' },
  { title: 'Documents', icon: 'bx-file', to: '/admin/documents' },
  { title: 'Imports', icon: 'bx-import', to: '/admin/imports' },
  { title: 'Chronologies', icon: 'bx-time', to: '/admin/chronologies' },
  { title: 'Send Email', icon: 'bx-envelope', to: '/admin/send-emails/create' },
  { title: 'Reports', icon: 'bx-bar-chart', to: '/admin/reports' },
  { title: 'Zoho', icon: 'bx-pen', to: '/admin/zoho' },
  { title: 'Failed Jobs', icon: 'bx-error', to: '/admin/failed-jobs' },
  { title: 'Terms', icon: 'bx-check-shield', to: '/admin/terms' },
  { title: 'Owner Statements', icon: 'bx-receipt', to: '/admin/owner-statements' },
]
</script>

<template>
  <VerticalNavLink
    v-for="item in items"
    :key="item.to"
    :item="item"
  />
</template>
