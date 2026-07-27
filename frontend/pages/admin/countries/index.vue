<script setup>
import { api } from '@/services/http'

definePageMeta({ middleware: 'auth' })

const entities = useAdminEntitiesStore()
const endpoint = '/admin/countries'
const fields = [
  { key: 'name', label: 'Name' },
  { key: 'code', label: 'Code' },
  { key: 'lat', label: 'Latitude', type: 'number', default: 0 },
  { key: 'lng', label: 'Longitude', type: 'number', default: 0 },
]

const editingId = ref(null)
const selectedCountryId = ref(null)
const confirmDelete = reactive({ open: false, id: null })
const emptyForm = () => Object.fromEntries(fields.map(field => [field.key, field.default ?? '']))
const form = reactive(emptyForm())
const stateForm = reactive({ name: '', code: '', lat: 0, lng: 0 })
const editingStateId = ref(null)
const stateErrors = ref({})

const rows = computed(() => entities.records[endpoint] || [])
const selectedCountry = computed(() => rows.value.find(row => row.id === selectedCountryId.value) || null)
const states = computed(() => selectedCountry.value?.states || [])

const resetForm = () => {
  editingId.value = null
  Object.assign(form, emptyForm())
}

const resetStateForm = () => {
  editingStateId.value = null
  Object.assign(stateForm, { name: '', code: '', lat: 0, lng: 0 })
  stateErrors.value = {}
}

const edit = row => {
  editingId.value = row.id
  selectedCountryId.value = row.id
  Object.assign(form, {
    name: row.name ?? '',
    code: row.code ?? '',
    lat: row.lat ?? 0,
    lng: row.lng ?? 0,
  })
  resetStateForm()
}

const selectCountry = row => {
  selectedCountryId.value = row.id
  resetStateForm()
}

const submit = async () => {
  try {
    const payload = {
      name: form.name,
      code: form.code,
      lat: form.lat === '' ? 0 : Number(form.lat),
      lng: form.lng === '' ? 0 : Number(form.lng),
    }

    if (editingId.value)
      await entities.update(endpoint, editingId.value, payload)
    else
      await entities.create(endpoint, payload)

    resetForm()
    await entities.list(endpoint)
  }
  catch {
    // Store exposes validation errors.
  }
}

const remove = async () => {
  try {
    await entities.remove(endpoint, confirmDelete.id)
    if (selectedCountryId.value === confirmDelete.id)
      selectedCountryId.value = null
    confirmDelete.open = false
    await entities.list(endpoint)
  }
  catch {
    // Store exposes errors.
  }
}

const editState = state => {
  editingStateId.value = state.id
  Object.assign(stateForm, {
    name: state.name ?? '',
    code: state.code ?? '',
    lat: state.lat ?? 0,
    lng: state.lng ?? 0,
  })
}

const submitState = async () => {
  if (!selectedCountryId.value)
    return

  stateErrors.value = {}

  try {
    const payload = {
      name: stateForm.name,
      code: stateForm.code,
      lat: stateForm.lat === '' ? 0 : Number(stateForm.lat),
      lng: stateForm.lng === '' ? 0 : Number(stateForm.lng),
    }

    if (editingStateId.value)
      await api.put(`/admin/countries/${selectedCountryId.value}/states/${editingStateId.value}`, payload)
    else
      await api.post(`/admin/countries/${selectedCountryId.value}/states`, payload)

    resetStateForm()
    await entities.list(endpoint)
  }
  catch (error) {
    stateErrors.value = error?.data?.errors || { general: [error?.data?.message || 'Unable to save state.'] }
  }
}

const removeState = async stateId => {
  if (!selectedCountryId.value)
    return

  try {
    await api.delete(`/admin/countries/${selectedCountryId.value}/states/${stateId}`)
    await entities.list(endpoint)
  }
  catch (error) {
    stateErrors.value = error?.data?.errors || { general: [error?.data?.message || 'Unable to delete state.'] }
  }
}

onMounted(() => entities.list(endpoint))
</script>

<template>
  <div>
    <PageHeader
      title="Countries & States"
      subtitle="Manage countries and their states"
    />

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard :title="editingId ? 'Edit country' : 'Add country'">
          <VCardText>
            <AppAlert :errors="entities.errors" />
            <VForm @submit.prevent="submit">
              <BaseInput
                v-model="form.name"
                label="Name"
                :error="entities.errors.name"
              />
              <BaseInput
                v-model="form.code"
                label="Code"
                :error="entities.errors.code"
              />
              <BaseInput
                v-model="form.lat"
                label="Latitude"
                type="number"
                :error="entities.errors.lat"
              />
              <BaseInput
                v-model="form.lng"
                label="Longitude"
                type="number"
                :error="entities.errors.lng"
              />
              <div class="d-flex flex-wrap gap-2 mt-2">
                <BaseButton
                  type="submit"
                  :label="editingId ? 'Update' : 'Create'"
                  :loading="entities.loading"
                />
                <BaseButton
                  v-if="editingId"
                  type="button"
                  variant="tonal"
                  label="Cancel"
                  @click="resetForm"
                />
              </div>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="Countries"
          :loading="entities.loading"
          :errors="entities.errors"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No countries found"
        >
          <template #head>
            <thead>
              <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Lat</th>
                <th>Lng</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>
          <tr
            v-for="row in rows"
            :key="row.id"
            :class="{ 'bg-grey-lighten-4': selectedCountryId === row.id }"
          >
            <td>{{ row.name }}</td>
            <td>{{ row.code }}</td>
            <td>{{ row.lat }}</td>
            <td>{{ row.lng }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="States"
                class="me-2"
                @click="selectCountry(row)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                class="me-2"
                @click="edit(row)"
              />
              <BaseButton
                size="small"
                variant="tonal"
                color="error"
                label="Delete"
                @click="Object.assign(confirmDelete, { open: true, id: row.id })"
              />
            </td>
          </tr>
        </DataTableShell>
      </VCol>
    </VRow>

    <VCard
      v-if="selectedCountry"
      class="mt-6"
      :title="`States for ${selectedCountry.name}`"
    >
      <VCardText>
        <AppAlert :errors="stateErrors" />
        <VRow>
          <VCol
            cols="12"
            md="4"
          >
            <VForm @submit.prevent="submitState">
              <BaseInput
                v-model="stateForm.name"
                label="State name"
                :error="stateErrors.name"
              />
              <BaseInput
                v-model="stateForm.code"
                label="State code"
                :error="stateErrors.code"
              />
              <BaseInput
                v-model="stateForm.lat"
                label="Latitude"
                type="number"
                :error="stateErrors.lat"
              />
              <BaseInput
                v-model="stateForm.lng"
                label="Longitude"
                type="number"
                :error="stateErrors.lng"
              />
              <div class="d-flex flex-wrap gap-2 mt-2">
                <BaseButton
                  type="submit"
                  :label="editingStateId ? 'Update state' : 'Add state'"
                />
                <BaseButton
                  v-if="editingStateId"
                  type="button"
                  variant="tonal"
                  label="Cancel"
                  @click="resetStateForm"
                />
              </div>
            </VForm>
          </VCol>
          <VCol
            cols="12"
            md="8"
          >
            <DataTableShell
              title="States"
              :empty="states.length === 0"
              :empty-colspan="5"
              empty-title="No states for this country"
            >
              <template #head>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Lat</th>
                    <th>Lng</th>
                    <th>Actions</th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="state in states"
                :key="state.id"
              >
                <td>{{ state.name }}</td>
                <td>{{ state.code }}</td>
                <td>{{ state.lat }}</td>
                <td>{{ state.lng }}</td>
                <td class="text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="editState(state)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="removeState(state.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete country?"
      message="This action cannot be undone."
      confirm-label="Delete"
      confirm-color="error"
      :loading="entities.loading"
      @confirm="remove"
    />
  </div>
</template>
