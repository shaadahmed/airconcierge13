<script setup>
import {
  CHARGE_AGREEMENT_LABEL,
  CLEANING_AGREEMENT_LABEL,
  CLEANING_POST_STAY_HINT,
  CLEANING_POST_STAY_OPTIONS,
  LATE_CANCELLATION_LABEL,
  TERMS_AGREEMENT_LABEL,
  UPDATE_TERMS_LABEL,
} from '@/constants/ownerBlocks'

const props = defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  loading: Boolean,
  mode: { type: String, default: 'create' },
  submitLabel: { type: String, default: 'Save' },
  propertyOptions: { type: Array, default: () => [] },
  showLateCancellation: Boolean,
})

const emit = defineEmits(['update:modelValue', 'submit', 'cancel'])

const isEdit = computed(() => props.mode === 'edit')

const form = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value,
  })
}

const fieldError = key => props.errors?.[key]

const onSubmit = () => emit('submit')
</script>

<template>
  <VForm @submit.prevent="onSubmit">
    <VRow>
      <template v-if="!isEdit">
        <VCol
          cols="12"
          md="6"
        >
          <BaseInput
            :model-value="form.first_name"
            label="Client Name"
            placeholder="First Name"
            required
            hint="First Name"
            persistent-hint
            :error="fieldError('first_name')"
            @update:model-value="updateField('first_name', $event)"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <BaseInput
            :model-value="form.last_name"
            label=" "
            placeholder="Last Name"
            hint="Last Name"
            persistent-hint
            :error="fieldError('last_name')"
            @update:model-value="updateField('last_name', $event)"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <BaseInput
            :model-value="form.email"
            label="Email"
            type="email"
            required
            :error="fieldError('email')"
            @update:model-value="updateField('email', $event)"
          />
        </VCol>
      </template>

      <VCol
        cols="12"
        :md="isEdit ? 12 : 6"
      >
        <BaseSelect
          :model-value="form.property_id"
          label="Property"
          placeholder="Select"
          required
          :items="propertyOptions"
          :error="fieldError('property_id')"
          @update:model-value="updateField('property_id', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.reservation_start_date"
          label="Owner Block Start Date"
          type="date"
          required
          hint="Remember check-in time is 4 PM"
          persistent-hint
          :error="fieldError('reservation_start_date')"
          @update:model-value="updateField('reservation_start_date', $event)"
        />
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.reservation_end_date"
          label="Owner Block End Date"
          type="date"
          required
          hint="Remember check-out time is 11 AM"
          persistent-hint
          :error="fieldError('reservation_end_date')"
          @update:model-value="updateField('reservation_end_date', $event)"
        />
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <div class="d-flex align-center gap-1 mb-1">
          <span class="text-body-2">
            Will you need cleaning post Owner stay
          </span>
          <button
            type="button"
            class="owner-block-form__hint-btn"
            :aria-label="CLEANING_POST_STAY_HINT"
          >
            <VIcon
              icon="bx-info-circle"
              size="16"
              color="primary"
            />
            <VTooltip
              activator="parent"
              location="top"
              max-width="320"
            >
              {{ CLEANING_POST_STAY_HINT }}
            </VTooltip>
          </button>
        </div>
        <BaseSelect
          :model-value="form.cleaning_post_order_stay"
          placeholder="Select"
          required
          hide-details="auto"
          :items="CLEANING_POST_STAY_OPTIONS"
          :error="fieldError('cleaning_post_order_stay')"
          @update:model-value="updateField('cleaning_post_order_stay', $event)"
        />
      </VCol>

      <VCol cols="12">
        <BaseTextarea
          :model-value="form.booking_notes"
          label="Comments"
          rows="3"
          :error="fieldError('booking_notes')"
          @update:model-value="updateField('booking_notes', $event)"
        />
      </VCol>

      <template v-if="!isEdit">
        <VCol cols="12">
          <BaseCheckbox
            :model-value="form.charge_agreement"
            :label="CHARGE_AGREEMENT_LABEL"
            :error="fieldError('charge_agreement')"
            @update:model-value="updateField('charge_agreement', $event)"
          />
        </VCol>
        <VCol cols="12">
          <div class="text-subtitle-2 mb-2">
            Terms of use
          </div>
          <BaseCheckbox
            :model-value="form.terms_agreement"
            :label="TERMS_AGREEMENT_LABEL"
            :error="fieldError('terms_agreement')"
            @update:model-value="updateField('terms_agreement', $event)"
          />
          <div class="text-body-2 mt-1">
            <BaseLink
              href="https://www.airconcierge.net/privacy-policy/"
              new-tab
            >
              Privacy policy
            </BaseLink>
            ·
            <BaseLink
              href="https://www.airconcierge.net/terms-of-use/"
              new-tab
            >
              Terms of use
            </BaseLink>
          </div>
        </VCol>
        <VCol cols="12">
          <div class="text-subtitle-2 mb-2">
            Cleaning agreement
          </div>
          <BaseCheckbox
            :model-value="form.cleaning_agreement"
            :label="CLEANING_AGREEMENT_LABEL"
            :error="fieldError('cleaning_agreement')"
            @update:model-value="updateField('cleaning_agreement', $event)"
          />
        </VCol>
      </template>

      <template v-else>
        <VCol
          v-if="showLateCancellation"
          cols="12"
        >
          <VAlert
            type="warning"
            variant="tonal"
            density="compact"
            class="mb-3"
          >
            Due to the last minute nature of this update, if you wish to continue, this may incur a cancel of cleaning charge ($100).
          </VAlert>
          <BaseCheckbox
            :model-value="form.late_cancellation_confirmation"
            :label="LATE_CANCELLATION_LABEL"
            :error="fieldError('late_cancellation_confirmation')"
            @update:model-value="updateField('late_cancellation_confirmation', $event)"
          />
        </VCol>
        <VCol cols="12">
          <BaseCheckbox
            :model-value="form.updation_confirmation"
            :label="UPDATE_TERMS_LABEL"
            :error="fieldError('updation_confirmation')"
            @update:model-value="updateField('updation_confirmation', $event)"
          />
        </VCol>
      </template>
    </VRow>

    <div class="d-flex flex-wrap gap-2 mt-6">
      <BaseButton
        type="submit"
        color="primary"
        :label="submitLabel"
        :loading="loading"
      />
      <BaseButton
        type="button"
        variant="tonal"
        label="Cancel"
        @click="emit('cancel')"
      />
    </div>
  </VForm>
</template>

<style scoped>
.owner-block-form__hint-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  background: transparent;
  cursor: help;
}
</style>
