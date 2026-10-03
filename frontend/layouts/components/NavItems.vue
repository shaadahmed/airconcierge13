<script setup>
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
import { adminNavigationGroups } from '@/navigation/adminNavigation'

const groups = adminNavigationGroups
const route = useRoute()

const isGroupOpen = group => {
  const children = group.children || []

  return children.some(child => child.to && route.path.startsWith(child.to))
}
</script>

<template>
  <VerticalNavGroup
    v-for="group in groups"
    :key="group.id || group.title"
    :item="{ title: group.title, icon: group.icon }"
    :default-open="isGroupOpen(group)"
  >
    <VerticalNavLink
      v-for="child in group.children"
      :key="child.to"
      :item="child"
    />
  </VerticalNavGroup>
</template>
