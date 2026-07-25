<script setup>
const props = defineProps({
  to: { type: [String, Object], default: null },
  href: { type: String, default: '' },
  newTab: { type: Boolean, default: false },
})

defineOptions({ inheritAttrs: false })

const isExternal = computed(() => Boolean(props.href) || (typeof props.to === 'string' && /^(?:https?:)?\/\//.test(props.to)))
const linkHref = computed(() => props.href || (typeof props.to === 'string' ? props.to : ''))
</script>

<template>
  <a
    v-if="isExternal"
    v-bind="$attrs"
    :href="linkHref"
    :target="newTab ? '_blank' : undefined"
    :rel="newTab ? 'noopener noreferrer' : undefined"
  ><slot /></a>
  <NuxtLink
    v-else
    v-bind="$attrs"
    :to="to"
    :target="newTab ? '_blank' : undefined"
    :rel="newTab ? 'noopener noreferrer' : undefined"
  >
    <slot />
  </NuxtLink>
</template>
