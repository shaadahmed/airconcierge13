<script setup>
defineOptions({ inheritAttrs: false })

defineProps({
  modelValue: { type: [String, Number, Boolean], default: null },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: Boolean,
  readonly: Boolean,
  required: Boolean,
  error: { type: [String, Array, Boolean], default: false },
  hint: { type: String, default: '' },
  size: { type: String, default: 'default' },
  variant: { type: String, default: 'default' },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <VRadio
    v-bind="$attrs"
    :model-value="modelValue"
    :label="label"
    :disabled="disabled || readonly"
    :density="size === 'small' ? 'compact' : 'default'"
    @update:model-value="$emit('update:modelValue', $event)"
  />
</template>
