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
})

defineEmits(['update:modelValue'])

defineOptions({ inheritAttrs: false })
</script>

<template>
  <VBtn
    v-bind="$attrs"
    :disabled="disabled || readonly"
    :size="size"
    :variant="variant"
  >
    <slot>{{ label }}</slot>
  </VBtn>
</template>
