<script setup>
/**
 * Edit-page satellite sections from legacy property edit.
 * UI shells only — data wiring comes with the backend phase.
 */
import {
  ADDITIONAL_FEE_APPLICATION_OPTIONS,
  ADDITIONAL_FEE_CURRENCY_OPTIONS,
  ADDITIONAL_FEE_TYPE_OPTIONS,
  LISTING_PLATFORM_OPTIONS,
  LISTING_STATUS_OPTIONS,
  labelFromOptions,
  propertyStatusLabel,
  yesNoLabel,
} from '@/constants/properties'

const props = defineProps({
  property: { type: Object, default: null },
})

const revenue = computed(() => props.property?.revenue_settings || {
  min_nightly_rate: '',
  min_nights_per_booking: '',
  owner_max_stay: '',
  booking_availability: '',
  notes: '',
  min_nightly_rate_by_ac: false,
  min_nights_per_booking_by_ac: false,
  owner_max_stay_by_ac: false,
  booking_availability_by_ac: false,
})

const paymentTypeFees = computed(() => props.property?.payment_type_fees || [])
const permits = computed(() => props.property?.permits || [])
const insurance = computed(() => props.property?.insurance || [])
const additionalFees = computed(() => props.property?.additional_fees || [])
const onlineListings = computed(() => props.property?.online_listings || [])
const owners = computed(() => props.property?.owners || [])
const childProperties = computed(() => props.property?.child_properties || [])

const money = value => {
  if (value === null || value === undefined || value === '')
    return '—'

  return `$${Number(value).toLocaleString()}`
}
</script>

<template>
  <div class="d-flex flex-column gap-4">
    <VAlert
      type="info"
      variant="tonal"
      density="compact"
    >
      Related sections below mirror the legacy edit screen. Actions are UI-only until the properties backend is built.
    </VAlert>

    <VCard>
      <VCardItem>
        <VCardTitle>Payment methods</VCardTitle>
      </VCardItem>
      <VCardText>
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Convenience fee ($)</th>
              <th>Administrative fee (%)</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in paymentTypeFees"
              :key="row.id"
            >
              <td>{{ row.name || '—' }}</td>
              <td>{{ money(row.convenience_fee) }}</td>
              <td>{{ row.administrative_fee != null ? `${row.administrative_fee}%` : '—' }}</td>
            </tr>
            <tr v-if="!paymentTypeFees.length">
              <td
                colspan="3"
                class="text-medium-emphasis"
              >
                No payment method fees yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <VCard>
      <VCardItem>
        <VCardTitle>Revenue management</VCardTitle>
      </VCardItem>
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <BaseInput
              :model-value="revenue.min_nightly_rate === -1 ? '' : revenue.min_nightly_rate"
              label="Min nightly rate"
              type="number"
              disabled
            />
            <BaseCheckbox
              :model-value="revenue.min_nightly_rate === -1"
              label="Use Air Concierge default"
              disabled
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <BaseInput
              :model-value="revenue.min_nights_per_booking === -1 ? '' : revenue.min_nights_per_booking"
              label="Min nights per booking"
              type="number"
              disabled
            />
            <BaseCheckbox
              :model-value="revenue.min_nights_per_booking === -1"
              label="Use Air Concierge default"
              disabled
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <BaseInput
              :model-value="revenue.owner_max_stay === -1 ? '' : revenue.owner_max_stay"
              label="Owner max stay"
              type="number"
              disabled
            />
            <BaseCheckbox
              :model-value="revenue.owner_max_stay === -1"
              label="Use Air Concierge default"
              disabled
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <BaseInput
              :model-value="revenue.booking_availability === -1 ? '' : revenue.booking_availability"
              label="Booking availability (days)"
              type="number"
              disabled
            />
            <BaseCheckbox
              :model-value="revenue.booking_availability === -1"
              label="Use Air Concierge default"
              disabled
            />
          </VCol>
          <VCol cols="12">
            <BaseTextarea
              :model-value="revenue.notes || ''"
              label="Revenue notes"
              rows="3"
              disabled
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard>
      <VCardItem>
        <VCardTitle>Permit information</VCardTitle>
        <template #append>
          <BaseButton
            size="small"
            variant="tonal"
            label="Add permit"
            disabled
          />
        </template>
      </VCardItem>
      <VCardText>
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Issue / renewal date</th>
              <th>Validity period</th>
              <th>Permit number</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in permits"
              :key="row.id"
            >
              <td>{{ row.issue_date || '—' }}</td>
              <td>{{ row.validity_period || '—' }}</td>
              <td>{{ row.permit_number || '—' }}</td>
              <td>{{ row.status || '—' }}</td>
            </tr>
            <tr v-if="!permits.length">
              <td
                colspan="4"
                class="text-medium-emphasis"
              >
                No permits yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <VCard>
      <VCardItem>
        <VCardTitle>Insurance information</VCardTitle>
        <template #append>
          <BaseButton
            size="small"
            variant="tonal"
            label="Add insurance"
            disabled
          />
        </template>
      </VCardItem>
      <VCardText>
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Carrier</th>
              <th>Insured name</th>
              <th>Policy #</th>
              <th>Expiry</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in insurance"
              :key="row.id"
            >
              <td>{{ row.carrier_name || '—' }}</td>
              <td>{{ row.insured_name || '—' }}</td>
              <td>{{ row.policy_no || '—' }}</td>
              <td>{{ row.expiry_date || '—' }}</td>
            </tr>
            <tr v-if="!insurance.length">
              <td
                colspan="4"
                class="text-medium-emphasis"
              >
                No insurance records yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <VCard>
      <VCardItem>
        <VCardTitle>Additional fees</VCardTitle>
        <template #append>
          <BaseButton
            size="small"
            variant="tonal"
            label="Add fee"
            disabled
          />
        </template>
      </VCardItem>
      <VCardText>
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Type</th>
              <th>Fee</th>
              <th>Currency</th>
              <th>Application</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in additionalFees"
              :key="row.id"
            >
              <td>{{ labelFromOptions(ADDITIONAL_FEE_TYPE_OPTIONS, row.additional_fee_type) }}</td>
              <td>{{ row.additional_fee ?? '—' }}</td>
              <td>{{ labelFromOptions(ADDITIONAL_FEE_CURRENCY_OPTIONS, row.additional_fee_currency_type) }}</td>
              <td>{{ labelFromOptions(ADDITIONAL_FEE_APPLICATION_OPTIONS, row.additional_fee_application) }}</td>
            </tr>
            <tr v-if="!additionalFees.length">
              <td
                colspan="4"
                class="text-medium-emphasis"
              >
                No additional fees yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <VCard>
      <VCardItem>
        <VCardTitle>Online listings</VCardTitle>
        <template #append>
          <BaseButton
            size="small"
            variant="tonal"
            label="Add listing"
            disabled
          />
        </template>
      </VCardItem>
      <VCardText>
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Platform</th>
              <th>Platform property ID</th>
              <th>URL</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in onlineListings"
              :key="row.id"
            >
              <td>{{ labelFromOptions(LISTING_PLATFORM_OPTIONS, row.listing_platform) }}</td>
              <td>{{ row.platform_property_id || '—' }}</td>
              <td>{{ row.listing_url || '—' }}</td>
              <td>{{ labelFromOptions(LISTING_STATUS_OPTIONS, row.listing_status) }}</td>
            </tr>
            <tr v-if="!onlineListings.length">
              <td
                colspan="4"
                class="text-medium-emphasis"
              >
                No online listings yet.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <VCard>
      <VCardItem>
        <VCardTitle>Owners listing</VCardTitle>
      </VCardItem>
      <VCardText>
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="owner in owners"
              :key="owner.id"
            >
              <td>{{ owner.full_name || [owner.first_name, owner.last_name].filter(Boolean).join(' ') || '—' }}</td>
              <td>{{ owner.owner_email || '—' }}</td>
            </tr>
            <tr v-if="!owners.length">
              <td
                colspan="2"
                class="text-medium-emphasis"
              >
                No linked owners.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <VCard>
      <VCardItem>
        <VCardTitle>Child properties</VCardTitle>
      </VCardItem>
      <VCardText>
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Title</th>
              <th>Status</th>
              <th>Co-host</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="child in childProperties"
              :key="child.id"
            >
              <td>{{ child.property_title || '—' }}</td>
              <td>{{ propertyStatusLabel(child.status) }}</td>
              <td>{{ yesNoLabel(child.property_cohost) }}</td>
            </tr>
            <tr v-if="!childProperties.length">
              <td
                colspan="3"
                class="text-medium-emphasis"
              >
                No child properties.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>
  </div>
</template>
