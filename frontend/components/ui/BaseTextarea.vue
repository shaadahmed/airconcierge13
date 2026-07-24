<script setup>
defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: Boolean,
  readonly: Boolean,
  required: Boolean,
  error: { type: [String, Array, Boolean], default: false },
  hint: { type: String, default: '' },
  size: { type: String, default: 'default' },
  variant: { type: String, default: 'outlined' },
})

defineEmits(['update:modelValue'])

const errorMessages = computed(() => props.error === true ? [] : props.error || [])
</script>

<template>
  <VTextarea
    v-bind="$attrs"
    :model-value="modelValue"
    :label="label"
    :placeholder="placeholder"
    :disabled="disabled"
    :readonly="readonly"
    :required="required"
    :error="error === true"
    :error-messages="errorMessages"
    :hint="hint"
    :density="size === 'small' ? 'compact' : 'default'"
    :variant="variant"
    @update:model-value="$emit('update:modelValue', $event)"
  />
</template>
