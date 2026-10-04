<script setup>
defineProps({
  modelValue: { type: [String, Number, Boolean, Object, Array], default: undefined },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: Boolean,
  readonly: Boolean,
  required: Boolean,
  error: { type: [String, Array, Boolean], default: false },
  hint: { type: String, default: '' },
  size: { type: String, default: 'default' },
  variant: { type: String, default: 'elevated' },
  to: { type: [String, Object], default: undefined },
  href: { type: String, default: undefined },
})

const emit = defineEmits(['click', 'update:modelValue'])

defineOptions({ inheritAttrs: false })
</script>

<template>
  <VBtn
    v-bind="$attrs"
    :to="to"
    :href="href"
    :disabled="disabled || readonly"
    :size="size"
    :variant="variant"
    @click="emit('click', $event)"
  >
    <slot>{{ label }}</slot>
  </VBtn>
</template>
