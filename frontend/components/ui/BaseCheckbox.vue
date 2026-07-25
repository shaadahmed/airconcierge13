<script setup>
const props = defineProps({
  modelValue: { type: [Boolean, Array], default: false },
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

defineOptions({ inheritAttrs: false })

const errorMessages = computed(() => props.error === true ? [] : props.error || [])
</script>

<template>
  <VCheckbox
    v-bind="$attrs"
    :model-value="modelValue"
    :label="label"
    :disabled="disabled"
    :readonly="readonly"
    :error="error === true"
    :error-messages="errorMessages"
    :hint="hint"
    :density="size === 'small' ? 'compact' : 'default'"
    @update:model-value="$emit('update:modelValue', $event)"
  />
</template>
