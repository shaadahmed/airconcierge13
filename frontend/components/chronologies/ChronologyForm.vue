<script setup>
const props = defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  loading: Boolean,
  submitLabel: { type: String, default: 'Save chronology' },
})

const emit = defineEmits(['update:modelValue', 'submit'])

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value,
  })
}
</script>

<template>
  <VForm @submit.prevent="$emit('submit')">
    <BaseInput
      :model-value="modelValue.name"
      label="Name"
      required
      :error="errors.name"
      @update:model-value="updateField('name', $event)"
    />
    <BaseInput
      :model-value="modelValue.startdate"
      label="Start date"
      type="date"
      required
      :error="errors.startdate"
      @update:model-value="updateField('startdate', $event)"
    />
    <BaseSelect
      :model-value="modelValue.chronologyoption"
      label="Chronology option"
      :items="[
        { title: 'Default', value: 0 },
        { title: 'Option 1', value: 1 },
        { title: 'Option 2', value: 2 },
      ]"
      :error="errors.chronologyoption"
      @update:model-value="updateField('chronologyoption', $event)"
    />
    <BaseButton
      type="submit"
      :label="submitLabel"
      :loading="loading"
    />
  </VForm>
</template>
