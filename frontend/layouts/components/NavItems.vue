<script setup>
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
import { navigationService } from '@/services/navigationService'

const groups = ref([])
const loading = ref(false)
const route = useRoute()

const loadNavigation = async () => {
  loading.value = true

  try {
    const response = await navigationService.list()

    groups.value = Array.isArray(response?.data) ? response.data : []
  }
  catch {
    groups.value = []
  }
  finally {
    loading.value = false
  }
}

const isGroupOpen = group => {
  const children = group.children || []

  return children.some(child => child.to && route.path.startsWith(child.to))
}

onMounted(loadNavigation)
</script>

<template>
  <template v-if="!loading">
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
</template>
