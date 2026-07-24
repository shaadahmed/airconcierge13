<script setup>
defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  loading: Boolean,
  submitLabel: { type: String, default: 'Save chronology' },
})

defineEmits(['submit'])
</script>

<template>
  <VForm @submit.prevent="$emit('submit')">
    <BaseInput
      v-model="modelValue.name"
      label="Name"
      required
      :error="errors.name"
    />
    <BaseInput
      v-model="modelValue.startdate"
      label="Start date"
      type="date"
      required
      :error="errors.startdate"
    />
    <BaseSelect
      v-model="modelValue.chronologyoption"
      label="Chronology option"
      :items="[
        { title: 'Default', value: 0 },
        { title: 'Option 1', value: 1 },
        { title: 'Option 2', value: 2 },
      ]"
      :error="errors.chronologyoption"
    />
    <BaseButton
      type="submit"
      :label="submitLabel"
      :loading="loading"
    />
  </VForm>
</template>
