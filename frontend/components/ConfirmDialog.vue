<script setup>
const props = defineProps({
  modelValue: Boolean,
  title: {
    type: String,
    default: 'Confirm',
  },
  message: {
    type: String,
    default: 'Are you sure?',
  },
  confirmLabel: {
    type: String,
    default: 'Confirm',
  },
  cancelLabel: {
    type: String,
    default: 'Cancel',
  },
  confirmColor: {
    type: String,
    default: 'primary',
  },
  loading: Boolean,
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const open = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const close = () => {
  open.value = false
  emit('cancel')
}

const confirm = () => {
  emit('confirm')
}
</script>

<template>
  <VDialog
    v-model="open"
    max-width="480"
  >
    <VCard>
      <VCardTitle>{{ title }}</VCardTitle>
      <VCardText>
        <slot>{{ message }}</slot>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <BaseButton
          variant="tonal"
          :label="cancelLabel"
          @click="close"
        />
        <BaseButton
          :color="confirmColor"
          :label="confirmLabel"
          :loading="loading"
          @click="confirm"
        />
      </VCardActions>
    </VCard>
  </VDialog>
</template>
