<script setup>
const route = useRoute()
const audits = usePropertyAuditsStore()
const properties = usePropertiesStore()

const visitPurposeOptions = [
  { title: 'Deliver Package/Supplies', value: 'Deliver Package/Supplies' },
  { title: 'Document Damage/Issue', value: 'Document Damage/Issue' },
  { title: 'Maintenance/Repair', value: 'Maintenance/Repair' },
  { title: 'Guest/Tenant Interaction', value: 'Guest/Tenant Interaction' },
  { title: 'Property Audit', value: 'Property Audit' },
  { title: 'Owner Meet Up', value: 'Owner Meet Up' },
  { title: 'Other', value: 'Other' },
]

const checklistFields = [
  { key: 'lockbox_key', label: 'Lockbox / key' },
  { key: 'backup_battery', label: 'Backup battery' },
  { key: 'garbage_can', label: 'Garbage can' },
  { key: 'owner_mail_disposed', label: 'Owner mail disposed' },
  { key: 'sinks_and_drains', label: 'Sinks and drains' },
  { key: 'exterior_walls', label: 'Exterior walls' },
  { key: 'exterior_doors_windows', label: 'Exterior doors / windows' },
  { key: 'lights', label: 'Lights' },
  { key: 'landscape', label: 'Landscape' },
  { key: 'damage_signs', label: 'Signs of damage' },
  { key: 'checked_smoke_detector', label: 'Checked smoke detector' },
  { key: 'replaced_smoke_detector', label: 'Replaced smoke detector' },
]

const form = reactive({
  property_id: null,
  visitor_name: '',
  visit_date: '',
  visit_purpose: [],
  other_visit_purpose: '',
  work_performed: '',
  guest_owner_notes: '',
  lockbox_key: false,
  lockbox_key_comment: '',
  backup_battery: false,
  backup_battery_comment: '',
  garbage_can: false,
  garbage_can_comment: '',
  owner_mail_disposed: false,
  owner_mail_disposed_comment: '',
  sinks_and_drains: false,
  sinks_and_drains_comment: '',
  exterior_walls: false,
  exterior_walls_comment: '',
  exterior_doors_windows: false,
  exterior_doors_windows_comment: '',
  lights: false,
  lights_comment: '',
  landscape: false,
  landscape_comment: '',
  damage_signs: false,
  damage_signs_comment: '',
  checked_smoke_detector: false,
  checked_smoke_detector_comment: '',
  replaced_smoke_detector: false,
  replaced_smoke_detector_comment: '',
  anything_else: '',
  hourly_work: false,
  no_of_hours: '',
  materials_cost: '',
  notes: '',
})

const propertyOptions = computed(() => {
  const data = properties.data?.data || properties.data || []
  const list = Array.isArray(data) ? data : []

  return list.map(property => ({
    title: property.property_title || `Property #${property.id}`,
    value: property.id,
  }))
})

const showOtherPurpose = computed(() => Array.isArray(form.visit_purpose) && form.visit_purpose.includes('Other'))
const showAuditTasks = computed(() => Array.isArray(form.visit_purpose) && form.visit_purpose.includes('Property Audit'))

onMounted(async () => {
  await properties.load()

  const propertyParam = route.query.property
  if (propertyParam)
    form.property_id = Number(propertyParam)
})

const submit = async () => {
  try {
    const purposes = [...(form.visit_purpose || [])]
    if (showOtherPurpose.value && form.other_visit_purpose.trim())
      purposes.push(form.other_visit_purpose.trim())

    const payload = {
      property_id: form.property_id,
      visitor_name: form.visitor_name || null,
      visit_date: form.visit_date || null,
      visit_purpose: purposes.join(','),
      work_performed: form.work_performed || null,
      guest_owner_notes: form.guest_owner_notes || null,
      anything_else: form.anything_else || null,
      hourly_work: Boolean(form.hourly_work),
      no_of_hours: form.no_of_hours === '' ? null : Number(form.no_of_hours),
      materials_cost: form.materials_cost === '' ? null : Number(form.materials_cost),
      notes: form.notes || null,
    }

    for (const field of checklistFields) {
      payload[field.key] = Boolean(form[field.key])
      payload[`${field.key}_comment`] = form[`${field.key}_comment`] || null
    }

    await audits.create(payload)
    await navigateTo('/admin/properties/audit/list')
  }
  catch {
    // Store exposes validation errors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Create audit report"
      subtitle="Add a property visit report"
    >
      <template #actions>
        <BaseButton
          variant="tonal"
          label="Back to list"
          to="/admin/properties/audit/list"
        />
      </template>
    </PageHeader>

    <VCard>
      <VCardText>
        <AppAlert :errors="audits.errors" />

        <VForm @submit.prevent="submit">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <BaseSelect
                v-model="form.property_id"
                label="Property"
                :items="propertyOptions"
                :error="audits.errors.property_id"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <BaseInput
                v-model="form.visitor_name"
                label="Visitor / manager name"
                :error="audits.errors.visitor_name"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <BaseInput
                v-model="form.visit_date"
                label="Date of visit"
                type="date"
                :error="audits.errors.visit_date"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <BaseSelect
                v-model="form.visit_purpose"
                label="Reason for visit"
                :items="visitPurposeOptions"
                multiple
                chips
                :error="audits.errors.visit_purpose"
              />
            </VCol>
            <VCol
              v-if="showOtherPurpose"
              cols="12"
            >
              <BaseInput
                v-model="form.other_visit_purpose"
                label="Other purpose (comma-separated if multiple)"
              />
            </VCol>
            <VCol cols="12">
              <BaseTextarea
                v-model="form.work_performed"
                label="Work performed"
                :error="audits.errors.work_performed"
              />
            </VCol>
            <VCol cols="12">
              <BaseTextarea
                v-model="form.guest_owner_notes"
                label="Guest / owner notes"
                :error="audits.errors.guest_owner_notes"
              />
            </VCol>
          </VRow>

          <template v-if="showAuditTasks">
            <VDivider class="my-4" />
            <div class="text-subtitle-1 mb-3">
              Detailed audit tasks
            </div>
            <VRow>
              <VCol
                v-for="field in checklistFields"
                :key="field.key"
                cols="12"
                md="6"
              >
                <BaseCheckbox
                  v-model="form[field.key]"
                  :label="field.label"
                />
                <BaseTextarea
                  v-if="form[field.key]"
                  v-model="form[`${field.key}_comment`]"
                  :label="`${field.label} comment`"
                  class="mt-2"
                />
              </VCol>
            </VRow>
          </template>

          <VDivider class="my-4" />

          <VRow>
            <VCol cols="12">
              <BaseTextarea
                v-model="form.anything_else"
                label="Anything else"
                :error="audits.errors.anything_else"
              />
            </VCol>
            <VCol cols="12">
              <BaseCheckbox
                v-model="form.hourly_work"
                label="Hourly work"
              />
            </VCol>
            <template v-if="form.hourly_work">
              <VCol
                cols="12"
                md="6"
              >
                <BaseInput
                  v-model="form.no_of_hours"
                  label="Number of hours"
                  type="number"
                  :error="audits.errors.no_of_hours"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <BaseInput
                  v-model="form.materials_cost"
                  label="Materials cost"
                  type="number"
                  :error="audits.errors.materials_cost"
                />
              </VCol>
            </template>
            <VCol cols="12">
              <BaseTextarea
                v-model="form.notes"
                label="Notes"
                :error="audits.errors.notes"
              />
            </VCol>
          </VRow>

          <div class="d-flex flex-wrap gap-2 mt-4">
            <BaseButton
              type="submit"
              label="Create audit report"
              :loading="audits.loading"
            />
            <BaseButton
              type="button"
              variant="tonal"
              label="Cancel"
              to="/admin/properties/audit/list"
            />
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
