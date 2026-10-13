<script setup>
import { BUTTON_INTENTS, inferButtonIntent } from '@/constants/uiColors'

const props = defineProps({
  modelValue: { type: [String, Number, Boolean, Object, Array], default: undefined },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: Boolean,
  readonly: Boolean,
  required: Boolean,
  error: { type: [String, Array, Boolean], default: false },
  hint: { type: String, default: '' },
  size: { type: String, default: 'default' },
  color: { type: String, default: undefined },
  variant: { type: String, default: undefined },
  /**
   * Semantic action styling. When omitted, inferred from `label`
   * (Create/Add New, Save/Update, Cancel, Delete). Pass `"none"` to opt out.
   */
  intent: { type: String, default: undefined },
  to: { type: [String, Object], default: undefined },
  href: { type: String, default: undefined },
})

const emit = defineEmits(['click', 'update:modelValue'])

defineOptions({ inheritAttrs: false })

const resolvedIntent = computed(() => {
  if (props.intent === 'none')
    return null

  if (props.intent && BUTTON_INTENTS[props.intent])
    return props.intent

  return inferButtonIntent(props.label)
})

const resolvedColor = computed(() => {
  const intent = resolvedIntent.value
  if (intent === 'create' || intent === 'save' || intent === 'cancel' || intent === 'delete')
    return BUTTON_INTENTS[intent].color

  return props.color
})

const resolvedVariant = computed(() => {
  const intent = resolvedIntent.value
  if (intent === 'cancel' || intent === 'delete')
    return BUTTON_INTENTS[intent].variant

  if (intent === 'create' || intent === 'save')
    return props.variant ?? BUTTON_INTENTS[intent].variant

  return props.variant ?? 'elevated'
})
</script>

<template>
  <VBtn
    v-bind="$attrs"
    :to="to"
    :href="href"
    :disabled="disabled || readonly"
    :size="size"
    :color="resolvedColor"
    :variant="resolvedVariant"
    @click="emit('click', $event)"
  >
    <slot>{{ label }}</slot>
  </VBtn>
</template>
