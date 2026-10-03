<script setup>
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
import { adminNavigationGroups } from '@/navigation/adminNavigation'

const groups = adminNavigationGroups
const route = useRoute()

const groupKey = group => group.id ?? group.title

const routeMatchedGroupKey = computed(() => {
  const match = groups.find(group =>
    (group.children || []).some(child => child.to && route.path.startsWith(child.to)),
  )

  return match ? groupKey(match) : null
})

const openGroupKey = ref(routeMatchedGroupKey.value)

watch(routeMatchedGroupKey, key => {
  if (key !== null)
    openGroupKey.value = key
})

const setGroupOpen = (group, open) => {
  const key = groupKey(group)

  openGroupKey.value = open ? key : null
}
</script>

<template>
  <VerticalNavGroup
    v-for="group in groups"
    :key="groupKey(group)"
    :item="{ title: group.title, icon: group.icon }"
    :open="openGroupKey === groupKey(group)"
    @update:open="setGroupOpen(group, $event)"
  >
    <VerticalNavLink
      v-for="child in group.children"
      :key="child.to"
      :item="child"
    />
  </VerticalNavGroup>
</template>
