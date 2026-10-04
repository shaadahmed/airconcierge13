<script setup>
import {
  AIRBNB_TOT_MODE_OPTIONS,
  MANAGEMENT_TYPE_OPTIONS,
  VRBO_TOT_MODE_OPTIONS,
  airbnbTotModeRequiresMethod,
  labelFromOptions,
  propertyStatusColor,
  propertyStatusLabel,
  vrboTotModeRequiresMethod,
  yesNoLabel,
} from '@/constants/properties'

const props = defineProps({
  property: { type: Object, required: true },
})

const ownerNames = property => {
  if (!Array.isArray(property?.owners) || !property.owners.length)
    return '—'

  return property.owners
    .map(owner => owner.full_name || [owner.first_name, owner.last_name].filter(Boolean).join(' ') || `#${owner.id}`)
    .join(', ')
}

const emailTitles = property => {
  if (Array.isArray(property?.email_titles) && property.email_titles.length)
    return property.email_titles.filter(Boolean).join(', ')

  return property?.email_title || '—'
}

const money = value => {
  if (value === null || value === undefined || value === '')
    return '—'

  const number = Number(value)

  return Number.isNaN(number) ? String(value) : `$${number.toLocaleString()}`
}

const cleanerNames = property => {
  if (!Array.isArray(property?.cleaners) || !property.cleaners.length)
    return '—'

  return property.cleaners
    .map(cleaner => cleaner.full_name || cleaner.name || `#${cleaner.id || cleaner}`)
    .join(', ')
}

/** Placeholder average rating until booking/review metrics are wired. Stable per property id. */
const propertyAverageRating = property => {
  if (property?.average_rating != null && property.average_rating !== '')
    return Number(property.average_rating).toFixed(1)

  const seed = Number(property?.id) || 0
  const tenths = (seed * 37 + 11) % 16 // 0..15 → 3.5..5.0

  return (3.5 + tenths / 10).toFixed(1)
}

const sections = computed(() => {
  const property = props.property
  const taxItems = [
    { label: 'Airbnb TOT mode', value: labelFromOptions(AIRBNB_TOT_MODE_OPTIONS, property.tot_mode) },
  ]

  if (airbnbTotModeRequiresMethod(property.tot_mode)) {
    taxItems.push({ label: 'Method of Airbnb TOT reporting', value: property.tot_method || '—' })
  }

  taxItems.push({
    label: 'VRBO / other platforms TOT mode',
    value: labelFromOptions(VRBO_TOT_MODE_OPTIONS, property.vrbo_tot_mode),
  })

  if (vrboTotModeRequiresMethod(property.vrbo_tot_mode)) {
    taxItems.push({ label: 'Method of VRBO TOT reporting', value: property.vrbo_tot_method || '—' })
  }

  taxItems.push(
    { label: 'Status', value: propertyStatusLabel(property.status), chip: propertyStatusColor(property.status) },
    { label: 'Payment method', value: property.payment_method || '—' },
    { label: 'Air Concierge pays cleaners', value: yesNoLabel(property.airconcierge_pays_cleaners) },
    { label: 'CO HOST property', value: yesNoLabel(property.property_cohost) },
    { label: 'Parent property', value: property.parent?.property_title || property.parent_id || '—' },
    { label: 'Primary residence', value: yesNoLabel(property.primary_residence) },
    { label: 'Secondary residence', value: yesNoLabel(property.secondary_residence) },
    { label: 'Apply resort fee', value: yesNoLabel(property.apply_resort_fee) },
  )

  return [
    {
      title: 'Owners & location',
      items: [
        { label: 'Owners', value: ownerNames(property) },
        { label: 'Region', value: property.region?.region_name || property.region?.name || property.region_id || '—' },
        { label: 'City / subregion', value: property.subregion?.name || property.subregion_id || '—' },
        { label: 'Management type', value: property.management_type?.name || labelFromOptions(MANAGEMENT_TYPE_OPTIONS, property.management_type_id) },
        { label: 'Property image URL', value: property.property_image_url || '—' },
      ],
    },
    {
      title: 'Address',
      items: [
        { label: 'Property address / title', value: property.property_title || '—' },
        { label: 'Hostaway listing ID', value: property.hostaway_listing_id || '—' },
        { label: 'Street address', value: property.street_address || '—' },
        { label: 'City', value: property.city || '—' },
        { label: 'State', value: property.state || '—' },
        { label: 'Zip code', value: property.zipcode || '—' },
        { label: 'Email titles', value: emailTitles(property) },
      ],
    },
    {
      title: 'Contract & support',
      items: [
        { label: 'Contract start date', value: property.contract_start_date || '—' },
        { label: 'Contract end date', value: property.contract_end_date || '—' },
        { label: 'Contract end reason', value: property.contract_end_reason || '—' },
        { label: 'Air Concierge support email', value: property.supportemail || '—' },
      ],
    },
    {
      title: 'Rooms',
      items: [
        { label: 'Bedrooms', value: property.bedrooms ?? '—' },
        { label: 'Bathrooms', value: property.bathrooms ?? '—' },
        { label: 'Other facilities', value: property.other_facilities || '—' },
      ],
    },
    {
      title: 'Fees',
      items: [
        { label: 'Default management fee (%)', value: property.ac_management_fee != null ? `${property.ac_management_fee}%` : '—' },
        { label: 'Standard exit cleaning fee', value: money(property.exit_cleaning_fee) },
        { label: 'Owners monthly costs', value: money(property.owners_montly_cost) },
        { label: 'Cleaners', value: cleanerNames(property) },
      ],
    },
    {
      title: 'Tax, payment & operations',
      items: taxItems,
    },
  ]
})
</script>

<template>
  <div>
    <VRow class="mb-4">
      <VCol
        cols="12"
        md="4"
      >
        <VImg
          v-if="property.property_image_url"
          :src="property.property_image_url"
          height="220"
          cover
          class="rounded-lg"
        />
        <div
          v-else
          class="property-view-placeholder rounded-lg d-flex align-center justify-center"
        >
          <VIcon
            icon="bx-home-alt"
            size="48"
            class="text-medium-emphasis"
          />
        </div>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <div class="d-flex align-center flex-wrap gap-2 mb-2">
          <h2 class="text-h5 mb-0">
            {{ property.property_title || 'Untitled property' }}
          </h2>
          <span class="property-view-rating">
            <VIcon
              icon="bx-bxs-star"
              size="16"
              color="warning"
            />
            {{ propertyAverageRating(property) }}
          </span>
          <VChip
            size="small"
            :color="propertyStatusColor(property.status)"
            label
          >
            {{ propertyStatusLabel(property.status) }}
          </VChip>
        </div>
        <p class="text-body-2 text-medium-emphasis mb-3">
          {{ [property.city, property.state].filter(Boolean).join(', ') || property.street_address || '—' }}
        </p>
        <div class="d-flex flex-wrap gap-4 text-body-2">
          <div class="d-flex align-center gap-2">
            <VIcon
              icon="bx-bed"
              size="18"
              class="text-medium-emphasis"
            />
            <span>{{ property.bedrooms ?? 0 }} beds</span>
          </div>
          <div class="d-flex align-center gap-2">
            <VIcon
              icon="bx-droplet"
              size="18"
              class="text-medium-emphasis"
            />
            <span>{{ property.bathrooms ?? 0 }} baths</span>
          </div>
          <div class="d-flex align-center gap-2">
            <VIcon
              icon="bx-map"
              size="18"
              class="text-medium-emphasis"
            />
            <span>{{ property.region?.name || property.region?.region_name || '—' }}</span>
          </div>
        </div>
      </VCol>
    </VRow>

    <VRow>
      <VCol
        v-for="section in sections"
        :key="section.title"
        cols="12"
        md="6"
      >
        <VCard
          variant="outlined"
          class="h-100"
        >
          <VCardItem>
            <VCardTitle class="text-subtitle-1">
              {{ section.title }}
            </VCardTitle>
          </VCardItem>
          <VCardText>
            <div
              v-for="item in section.items"
              :key="item.label"
              class="property-view-row"
            >
              <div class="text-caption text-medium-emphasis">
                {{ item.label }}
              </div>
              <div class="text-body-2">
                <VChip
                  v-if="item.chip"
                  size="x-small"
                  :color="item.chip"
                  label
                >
                  {{ item.value }}
                </VChip>
                <span v-else>{{ item.value }}</span>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12">
        <VCard variant="outlined">
          <VCardItem>
            <VCardTitle class="text-subtitle-1">
              Notes (regarding management)
            </VCardTitle>
          </VCardItem>
          <VCardText>
            <p class="text-body-2 mb-0 whitespace-pre-wrap">
              {{ property.management_notes || '—' }}
            </p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.property-view-placeholder {
  height: 220px;
  background: rgba(75, 70, 92, 0.06);
}

.property-view-rating {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  font-size: 0.875rem;
  font-weight: 600;
  line-height: 1;
  color: rgb(var(--v-theme-on-surface));
}

.property-view-row {
  display: grid;
  grid-template-columns: minmax(140px, 40%) 1fr;
  gap: 8px 16px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(75, 70, 92, 0.08);
}

.property-view-row:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.whitespace-pre-wrap {
  white-space: pre-wrap;
}
</style>
