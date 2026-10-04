<script setup>
import {
  AIRBNB_TO_VRBO_TOT_MODE,
  AIRBNB_TOT_MODE_OPTIONS,
  airbnbTotModeRequiresMethod,
  CONTRACT_END_REASON_OPTIONS,
  MANAGEMENT_TYPE_OPTIONS,
  PAYMENT_METHOD_OPTIONS,
  PROPERTY_STATUS_CREATE_OPTIONS,
  PROPERTY_STATUS_OPTIONS,
  TOT_METHOD_OPTIONS,
  US_STATE_OPTIONS,
  VRBO_TO_AIRBNB_TOT_MODE,
  VRBO_TOT_MODE_OPTIONS,
  vrboTotModeRequiresMethod,
  YES_NO_OPTIONS,
} from '@/constants/properties'

const props = defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  loading: Boolean,
  readonly: Boolean,
  mode: { type: String, default: 'create' },
  submitLabel: { type: String, default: 'Save property' },
  ownerOptions: { type: Array, default: () => [] },
  regionOptions: { type: Array, default: () => [] },
  subregionOptions: { type: Array, default: () => [] },
  parentPropertyOptions: { type: Array, default: () => [] },
  cleanerOptions: { type: Array, default: () => [] },
  showResortFee: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue', 'submit', 'cancel'])

const form = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const statusOptions = computed(() =>
  props.mode === 'edit' ? PROPERTY_STATUS_OPTIONS : PROPERTY_STATUS_CREATE_OPTIONS,
)

const showAirbnbTotMethod = computed(() => airbnbTotModeRequiresMethod(form.value.tot_mode))
const showVrboTotMethod = computed(() => vrboTotModeRequiresMethod(form.value.vrbo_tot_mode))

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value,
  })
}

const onAirbnbTotModeChange = value => {
  const vrboMode = value == null || value === ''
    ? null
    : (AIRBNB_TO_VRBO_TOT_MODE[value] ?? null)

  emit('update:modelValue', {
    ...props.modelValue,
    tot_mode: value,
    vrbo_tot_mode: vrboMode,
    tot_method: airbnbTotModeRequiresMethod(value) ? props.modelValue.tot_method : null,
    vrbo_tot_method: vrboTotModeRequiresMethod(vrboMode) ? props.modelValue.vrbo_tot_method : null,
  })
}

const onVrboTotModeChange = value => {
  const airbnbMode = value == null || value === ''
    ? null
    : (VRBO_TO_AIRBNB_TOT_MODE[String(value)] ?? null)

  emit('update:modelValue', {
    ...props.modelValue,
    vrbo_tot_mode: value,
    tot_mode: airbnbMode,
    tot_method: airbnbTotModeRequiresMethod(airbnbMode) ? props.modelValue.tot_method : null,
    vrbo_tot_method: vrboTotModeRequiresMethod(value) ? props.modelValue.vrbo_tot_method : null,
  })
}

const updateEmailTitle = (index, value) => {
  const emailTitles = [...(props.modelValue.email_titles || [''])]
  emailTitles[index] = value
  updateField('email_titles', emailTitles)
}

const addEmailTitle = () => {
  updateField('email_titles', [...(props.modelValue.email_titles || ['']), ''])
}

const removeEmailTitle = index => {
  const emailTitles = [...(props.modelValue.email_titles || [''])]
  if (emailTitles.length <= 1)
    return

  emailTitles.splice(index, 1)
  updateField('email_titles', emailTitles)
}

const onRegionChange = value => {
  emit('update:modelValue', {
    ...props.modelValue,
    region_id: value,
    subregion_id: null,
    parent_id: null,
  })
}

const fieldError = key => props.errors?.[key]
</script>

<template>
  <VForm
    :disabled="readonly"
    @submit.prevent="!readonly && $emit('submit')"
  >
    <VRow>
      <!-- Identity / location -->
      <VCol cols="12">
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Owners & location
        </div>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Core ownership and geography fields from the legacy property form.
        </p>
      </VCol>

      <VCol
        cols="12"
        md="6"
        lg="4"
      >
        <BaseSelect
          :model-value="form.owners"
          label="Owners"
          :items="ownerOptions"
          item-title="title"
          item-value="value"
          multiple
          chips
          closable-chips
          required
          :error="fieldError('owners')"
          @update:model-value="updateField('owners', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
        lg="4"
      >
        <BaseSelect
          :model-value="form.region_id"
          label="Region"
          :items="regionOptions"
          clearable
          required
          :error="fieldError('region_id')"
          @update:model-value="onRegionChange"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
        lg="4"
      >
        <BaseSelect
          :model-value="form.subregion_id"
          label="City / subregion"
          :items="subregionOptions"
          clearable
          required
          :error="fieldError('subregion_id')"
          @update:model-value="updateField('subregion_id', $event)"
        />
      </VCol>

      <template v-if="mode === 'edit'">
        <VCol
          cols="12"
          md="6"
          lg="4"
        >
          <BaseSelect
            :model-value="form.management_type_id"
            label="Management type"
            :items="MANAGEMENT_TYPE_OPTIONS"
            required
            :error="fieldError('management_type_id')"
            @update:model-value="updateField('management_type_id', $event)"
          />
        </VCol>

        <VCol
          cols="12"
          md="6"
          lg="4"
        >
          <BaseInput
            :model-value="form.property_image_url"
            label="Property image URL"
            :error="fieldError('property_image_url')"
            @update:model-value="updateField('property_image_url', $event)"
          />
        </VCol>
      </template>

      <!-- Address -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Address
        </div>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <BaseInput
          :model-value="form.property_title"
          label="Property address / title"
          required
          :error="fieldError('property_title')"
          @update:model-value="updateField('property_title', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.hostaway_listing_id"
          label="Hostaway listing ID"
          :error="fieldError('hostaway_listing_id')"
          @update:model-value="updateField('hostaway_listing_id', $event)"
        />
      </VCol>

      <VCol cols="12">
        <BaseInput
          :model-value="form.street_address"
          label="Street address"
          :error="fieldError('street_address')"
          @update:model-value="updateField('street_address', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.city"
          label="City"
          :error="fieldError('city')"
          @update:model-value="updateField('city', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseSelect
          :model-value="form.state"
          label="State"
          :items="US_STATE_OPTIONS"
          clearable
          :error="fieldError('state')"
          @update:model-value="updateField('state', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.zipcode"
          label="Zip code"
          :error="fieldError('zipcode')"
          @update:model-value="updateField('zipcode', $event)"
        />
      </VCol>

      <VCol
        v-for="(title, index) in form.email_titles"
        :key="`email-title-${index}`"
        cols="12"
        md="4"
      >
        <div class="d-flex align-end gap-2">
          <BaseInput
            class="flex-grow-1"
            :model-value="title"
            :label="index === 0 ? 'Email title' : `Email title ${index + 1}`"
            :error="index === 0 ? fieldError('email_title') || fieldError('email_titles') : false"
            @update:model-value="updateEmailTitle(index, $event)"
          />
          <VBtn
            v-if="!readonly && index === 0"
            type="button"
            icon
            variant="tonal"
            color="primary"
            aria-label="Add email title"
            @click="addEmailTitle"
          >
            <VIcon icon="bx-plus" />
          </VBtn>
          <VBtn
            v-else-if="!readonly"
            type="button"
            icon
            variant="tonal"
            color="error"
            aria-label="Remove email title"
            @click="removeEmailTitle(index)"
          >
            <VIcon icon="bx-minus" />
          </VBtn>
        </div>
      </VCol>

      <!-- Contract / support -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Contract & support
        </div>
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.contract_start_date"
          label="Contract start date"
          type="date"
          :error="fieldError('contract_start_date')"
          @update:model-value="updateField('contract_start_date', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.contract_end_date"
          label="Contract end date"
          type="date"
          :error="fieldError('contract_end_date')"
          @update:model-value="updateField('contract_end_date', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.contract_end_reason"
          label="Contract end reason"
          :items="CONTRACT_END_REASON_OPTIONS"
          clearable
          :error="fieldError('contract_end_reason')"
          @update:model-value="updateField('contract_end_reason', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseInput
          :model-value="form.supportemail"
          label="Air Concierge support email"
          type="email"
          required
          :error="fieldError('supportemail')"
          @update:model-value="updateField('supportemail', $event)"
        />
      </VCol>

      <!-- Rooms -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Rooms
        </div>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.bedrooms"
          label="Bedrooms"
          type="number"
          :error="fieldError('bedrooms')"
          @update:model-value="updateField('bedrooms', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.bathrooms"
          label="Bathrooms"
          type="number"
          :error="fieldError('bathrooms')"
          @update:model-value="updateField('bathrooms', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.other_facilities"
          label="Other facilities"
          hint="e.g. pool, lawn, BBQ area"
          persistent-hint
          :error="fieldError('other_facilities')"
          @update:model-value="updateField('other_facilities', $event)"
        />
      </VCol>

      <!-- Fees -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Fees
        </div>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.ac_management_fee"
          label="Default management fee (%)"
          type="number"
          :error="fieldError('ac_management_fee')"
          @update:model-value="updateField('ac_management_fee', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.exit_cleaning_fee"
          label="Standard exit cleaning fee"
          type="number"
          required
          :error="fieldError('exit_cleaning_fee')"
          @update:model-value="updateField('exit_cleaning_fee', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <BaseInput
          :model-value="form.owners_montly_cost"
          label="Owners monthly costs"
          hint="Mortgage, taxes, insurance, utilities"
          persistent-hint
          type="number"
          :error="fieldError('owners_montly_cost')"
          @update:model-value="updateField('owners_montly_cost', $event)"
        />
      </VCol>

      <VCol
        v-if="mode === 'edit'"
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.cleaners"
          label="Cleaners"
          :items="cleanerOptions"
          multiple
          chips
          closable-chips
          clearable
          :error="fieldError('cleaners')"
          @update:model-value="updateField('cleaners', $event)"
        />
      </VCol>

      <!-- Tax / ops -->
      <VCol
        cols="12"
        class="mt-2"
      >
        <VDivider class="mb-4" />
        <div class="text-subtitle-1 font-weight-medium mb-1">
          Tax, payment & operations
        </div>
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.tot_mode"
          label="Airbnb TOT mode"
          :items="AIRBNB_TOT_MODE_OPTIONS"
          required
          :error="fieldError('tot_mode')"
          @update:model-value="onAirbnbTotModeChange"
        />
      </VCol>

      <VCol
        v-if="showAirbnbTotMethod"
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.tot_method"
          label="Method of Airbnb TOT reporting"
          :items="TOT_METHOD_OPTIONS"
          required
          :error="fieldError('tot_method')"
          @update:model-value="updateField('tot_method', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.vrbo_tot_mode"
          label="VRBO / other platforms TOT mode"
          :items="VRBO_TOT_MODE_OPTIONS"
          required
          :error="fieldError('vrbo_tot_mode')"
          @update:model-value="onVrboTotModeChange"
        />
      </VCol>

      <VCol
        v-if="showVrboTotMethod"
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.vrbo_tot_method"
          label="Method of VRBO TOT reporting"
          :items="TOT_METHOD_OPTIONS"
          required
          :error="fieldError('vrbo_tot_method')"
          @update:model-value="updateField('vrbo_tot_method', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.status"
          label="Status"
          :items="statusOptions"
          required
          :error="fieldError('status')"
          @update:model-value="updateField('status', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.payment_method"
          label="Payment method"
          :items="PAYMENT_METHOD_OPTIONS"
          clearable
          :error="fieldError('payment_method')"
          @update:model-value="updateField('payment_method', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.airconcierge_pays_cleaners"
          label="Air Concierge pays cleaners"
          :items="YES_NO_OPTIONS"
          required
          :error="fieldError('airconcierge_pays_cleaners')"
          @update:model-value="updateField('airconcierge_pays_cleaners', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.property_cohost"
          label="CO HOST property?"
          :items="YES_NO_OPTIONS"
          required
          :error="fieldError('property_cohost')"
          @update:model-value="updateField('property_cohost', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <BaseSelect
          :model-value="form.parent_id"
          label="Parent property"
          :items="parentPropertyOptions"
          clearable
          hint="Leave empty if this is a parent/main property."
          persistent-hint
          :error="fieldError('parent_id')"
          @update:model-value="updateField('parent_id', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="3"
      >
        <BaseCheckbox
          :model-value="form.primary_residence"
          label="Primary residence"
          :error="fieldError('primary_residence')"
          @update:model-value="updateField('primary_residence', $event)"
        />
      </VCol>

      <VCol
        cols="12"
        md="3"
      >
        <BaseCheckbox
          :model-value="form.secondary_residence"
          label="Secondary residence"
          :error="fieldError('secondary_residence')"
          @update:model-value="updateField('secondary_residence', $event)"
        />
      </VCol>

      <VCol
        v-if="mode === 'edit' && showResortFee"
        cols="12"
        md="6"
      >
        <BaseCheckbox
          :model-value="form.apply_resort_fee"
          label="Apply resort fee"
          :error="fieldError('apply_resort_fee')"
          @update:model-value="updateField('apply_resort_fee', $event)"
        />
      </VCol>

      <VCol cols="12">
        <BaseTextarea
          :model-value="form.management_notes"
          label="Notes (regarding management)"
          rows="5"
          :error="fieldError('management_notes')"
          @update:model-value="updateField('management_notes', $event)"
        />
      </VCol>
    </VRow>

    <div class="d-flex flex-wrap gap-2 mt-4">
      <BaseButton
        v-if="!readonly"
        type="submit"
        :label="submitLabel"
        :loading="loading"
      />
      <BaseButton
        type="button"
        variant="tonal"
        :label="readonly ? 'Back' : 'Cancel'"
        @click="$emit('cancel')"
      />
    </div>
  </VForm>
</template>
