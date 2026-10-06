<script setup>
const countries = useCountriesStore()

const form = reactive({
  name: '',
  code: '',
  lat: 0,
  lng: 0,
})

const stateForm = reactive({
  name: '',
  code: '',
  lat: 0,
  lng: 0,
})

const cityForm = reactive({
  name: '',
  code: '',
  zip: '',
  contact_code: '',
})

const countrySearch = ref('')
const stateSearch = ref('')
const citySearch = ref('')
const editingId = ref(null)
const editingStateId = ref(null)
const editingCityId = ref(null)
const selectedCountryId = ref(null)
const selectedStateId = ref(null)
const countryDialogOpen = ref(false)
const stateDialogOpen = ref(false)
const cityDialogOpen = ref(false)
const confirmDelete = reactive({ open: false, id: null, type: 'country' })

const allRows = computed(() => {
  const data = countries.data?.data || countries.data || []

  return Array.isArray(data) ? data : []
})

const stateCount = country => Array.isArray(country.states) ? country.states.length : 0
const cityCount = state => Array.isArray(state.cities) ? state.cities.length : 0

const filteredCountries = computed(() => {
  const query = countrySearch.value.trim().toLowerCase()

  if (!query)
    return allRows.value

  return allRows.value.filter(country => {
    const name = String(country.name || '').toLowerCase()
    const code = String(country.code || '').toLowerCase()

    return name.includes(query) || code.includes(query)
  })
})

const selectedCountry = computed(() => allRows.value.find(row => row.id === selectedCountryId.value) || null)

const filteredStates = computed(() => {
  const list = selectedCountry.value?.states || []
  const query = stateSearch.value.trim().toLowerCase()

  if (!query)
    return list

  return list.filter(state => {
    const name = String(state.name || '').toLowerCase()
    const code = String(state.code || '').toLowerCase()

    return name.includes(query) || code.includes(query)
  })
})

const selectedState = computed(() => {
  if (!selectedCountry.value || selectedStateId.value == null)
    return null

  return (selectedCountry.value.states || []).find(state => state.id === selectedStateId.value) || null
})

const filteredCities = computed(() => {
  const list = selectedState.value?.cities || []
  const query = citySearch.value.trim().toLowerCase()

  if (!query)
    return list

  return list.filter(city => {
    const name = String(city.name || '').toLowerCase()
    const code = String(city.code || '').toLowerCase()

    return name.includes(query) || code.includes(query)
  })
})

onMounted(() => countries.load())

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    name: '',
    code: '',
    lat: 0,
    lng: 0,
  })
  countries.errors = {}
}

const resetStateForm = () => {
  editingStateId.value = null
  Object.assign(stateForm, {
    name: '',
    code: '',
    lat: 0,
    lng: 0,
  })
  countries.stateErrors = {}
}

const resetCityForm = () => {
  editingCityId.value = null
  Object.assign(cityForm, {
    name: '',
    code: '',
    zip: '',
    contact_code: '',
  })
  countries.cityErrors = {}
}

const openCreateCountry = () => {
  resetForm()
  countryDialogOpen.value = true
}

const editCountry = country => {
  editingId.value = country.id
  Object.assign(form, {
    name: country.name ?? '',
    code: country.code ?? '',
    lat: country.lat ?? 0,
    lng: country.lng ?? 0,
  })
  countries.errors = {}
  countryDialogOpen.value = true
}

const selectCountry = country => {
  selectedCountryId.value = country.id
  selectedStateId.value = null
  stateSearch.value = ''
  citySearch.value = ''
  resetStateForm()
  resetCityForm()
}

const selectState = state => {
  selectedStateId.value = state.id
  citySearch.value = ''
  resetCityForm()
}

const cancelCountryDialog = () => {
  countryDialogOpen.value = false
  resetForm()
}

const submitCountry = async () => {
  try {
    const payload = {
      name: form.name,
      code: form.code,
      lat: form.lat === '' ? 0 : Number(form.lat),
      lng: form.lng === '' ? 0 : Number(form.lng),
    }

    if (editingId.value)
      await countries.update(editingId.value, payload)
    else
      await countries.create(payload)

    countryDialogOpen.value = false
    resetForm()
    if (!countries.usingMocks)
      await countries.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

const askDeleteCountry = id => {
  confirmDelete.open = true
  confirmDelete.id = id
  confirmDelete.type = 'country'
}

const openCreateState = () => {
  if (!selectedCountryId.value)
    return

  resetStateForm()
  stateDialogOpen.value = true
}

const editState = state => {
  editingStateId.value = state.id
  Object.assign(stateForm, {
    name: state.name ?? '',
    code: state.code ?? '',
    lat: state.lat ?? 0,
    lng: state.lng ?? 0,
  })
  countries.stateErrors = {}
  stateDialogOpen.value = true
}

const cancelStateDialog = () => {
  stateDialogOpen.value = false
  resetStateForm()
}

const submitState = async () => {
  if (!selectedCountryId.value)
    return

  try {
    const payload = {
      name: stateForm.name,
      code: stateForm.code,
      lat: stateForm.lat === '' ? 0 : Number(stateForm.lat),
      lng: stateForm.lng === '' ? 0 : Number(stateForm.lng),
    }

    if (editingStateId.value)
      await countries.updateState(selectedCountryId.value, editingStateId.value, payload)
    else
      await countries.createState(selectedCountryId.value, payload)

    stateDialogOpen.value = false
    resetStateForm()
    if (!countries.usingMocks)
      await countries.load()
  }
  catch {
    // Store exposes stateErrors.
  }
}

const askDeleteState = id => {
  confirmDelete.open = true
  confirmDelete.id = id
  confirmDelete.type = 'state'
}

const openCreateCity = () => {
  if (!selectedCountryId.value || !selectedStateId.value)
    return

  resetCityForm()
  cityDialogOpen.value = true
}

const editCity = city => {
  editingCityId.value = city.id
  Object.assign(cityForm, {
    name: city.name ?? '',
    code: city.code ?? '',
    zip: city.zip ?? '',
    contact_code: city.contact_code ?? '',
  })
  countries.cityErrors = {}
  cityDialogOpen.value = true
}

const cancelCityDialog = () => {
  cityDialogOpen.value = false
  resetCityForm()
}

const submitCity = async () => {
  if (!selectedCountryId.value || !selectedStateId.value)
    return

  try {
    const payload = {
      name: cityForm.name,
      code: cityForm.code || null,
      zip: cityForm.zip === '' || cityForm.zip == null ? null : Number(cityForm.zip),
      contact_code: cityForm.contact_code || null,
    }

    if (editingCityId.value) {
      await countries.updateCity(
        selectedCountryId.value,
        selectedStateId.value,
        editingCityId.value,
        payload,
      )
    }
    else {
      await countries.createCity(selectedCountryId.value, selectedStateId.value, payload)
    }

    cityDialogOpen.value = false
    resetCityForm()
    if (!countries.usingMocks)
      await countries.load()
  }
  catch {
    // Store exposes cityErrors.
  }
}

const askDeleteCity = id => {
  confirmDelete.open = true
  confirmDelete.id = id
  confirmDelete.type = 'city'
}

const onDelete = async () => {
  try {
    if (confirmDelete.type === 'country') {
      await countries.remove(confirmDelete.id)
      if (selectedCountryId.value === confirmDelete.id) {
        selectedCountryId.value = null
        selectedStateId.value = null
      }
    }
    else if (confirmDelete.type === 'state' && selectedCountryId.value) {
      await countries.removeState(selectedCountryId.value, confirmDelete.id)
      if (selectedStateId.value === confirmDelete.id)
        selectedStateId.value = null
    }
    else if (confirmDelete.type === 'city' && selectedCountryId.value && selectedStateId.value) {
      await countries.removeCity(selectedCountryId.value, selectedStateId.value, confirmDelete.id)
    }

    confirmDelete.open = false
    if (!countries.usingMocks)
      await countries.load()
  }
  catch {
    // Store exposes errors.
  }
}

const deleteTitle = computed(() => {
  if (confirmDelete.type === 'state')
    return 'Delete state?'
  if (confirmDelete.type === 'city')
    return 'Delete city?'

  return 'Delete country?'
})

const deleteMessage = computed(() => {
  if (confirmDelete.type === 'state')
    return 'This soft-deletes the selected state and its cities.'
  if (confirmDelete.type === 'city')
    return 'This soft-deletes the selected city.'

  return 'This deletes the country and soft-deletes its states and cities.'
})

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Countries, States & Cities"
      subtitle="Select a country, then a state, to manage cities"
    />

    <VAlert
      v-if="countries.usingMocks"
      type="info"
      variant="tonal"
      class="mb-4"
      density="compact"
    >
      Showing UI preview data until the countries API is connected.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard class="geo-panel">
          <VCardText class="pb-2">
            <div class="d-flex align-center justify-space-between mb-3">
              <div class="text-h6">
                Countries ({{ filteredCountries.length }})
              </div>
              <VBtn
                icon
                size="small"
                color="success"
                rounded="circle"
                aria-label="Create country"
                @click="openCreateCountry"
              >
                <VIcon icon="bx-plus" />
              </VBtn>
            </div>

            <BaseInput
              v-model="countrySearch"
              placeholder="Search countries..."
              prepend-inner-icon="bx-search"
              size="small"
              hide-details
              class="mb-2"
            />
          </VCardText>

          <VDivider />

          <div
            v-if="countries.loading && !allRows.length"
            class="geo-panel__body d-flex align-center justify-center"
          >
            <VProgressCircular indeterminate />
          </div>

          <div
            v-else-if="filteredCountries.length === 0"
            class="geo-panel__body d-flex align-center justify-center"
          >
            <EmptyState
              :title="allRows.length ? 'No countries match your search' : 'No countries yet'"
              :description="allRows.length ? '' : 'Create a country to get started.'"
            />
          </div>

          <div
            v-else
            class="geo-panel__body"
          >
            <button
              v-for="country in filteredCountries"
              :key="country.id"
              type="button"
              class="geo-row"
              :class="{ 'geo-row--active': selectedCountryId === country.id }"
              @click="selectCountry(country)"
            >
              <div class="geo-row__main">
                <div class="geo-row__title">
                  {{ country.name }}
                </div>
                <div class="geo-row__meta">
                  Code: {{ country.code || '—' }}
                </div>
              </div>

              <VChip
                size="small"
                variant="tonal"
                class="me-2"
              >
                {{ stateCount(country) }} {{ stateCount(country) === 1 ? 'state' : 'states' }}
              </VChip>

              <VBtn
                icon
                size="x-small"
                variant="text"
                color="primary"
                aria-label="Edit country"
                @click.stop="editCountry(country)"
              >
                <VIcon
                  icon="bx-pencil"
                  size="18"
                />
              </VBtn>

              <VBtn
                icon
                size="x-small"
                variant="text"
                color="error"
                aria-label="Delete country"
                @click.stop="askDeleteCountry(country.id)"
              >
                <VIcon
                  icon="bx-trash"
                  size="18"
                />
              </VBtn>
            </button>
          </div>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard class="geo-panel">
          <VCardText class="pb-2">
            <div class="d-flex align-center justify-space-between mb-3">
              <div class="text-h6">
                States
                <span
                  v-if="selectedCountry"
                  class="text-medium-emphasis"
                >
                  ({{ filteredStates.length }})
                </span>
              </div>
              <VBtn
                icon
                size="small"
                color="success"
                rounded="circle"
                :disabled="!selectedCountry"
                aria-label="Create state"
                @click="openCreateState"
              >
                <VIcon icon="bx-plus" />
              </VBtn>
            </div>

            <BaseInput
              v-model="stateSearch"
              placeholder="Search states..."
              prepend-inner-icon="bx-search"
              size="small"
              hide-details
              :disabled="!selectedCountry"
              class="mb-2"
            />
          </VCardText>

          <VDivider />

          <div
            v-if="!selectedCountry"
            class="geo-panel__body d-flex align-center justify-center"
          >
            <EmptyState title="Select a country to view its states" />
          </div>

          <div
            v-else-if="filteredStates.length === 0"
            class="geo-panel__body d-flex align-center justify-center"
          >
            <EmptyState
              :title="(selectedCountry.states || []).length ? 'No states match your search' : 'No states for this country'"
              :description="(selectedCountry.states || []).length ? '' : 'Use + to add a state.'"
            />
          </div>

          <div
            v-else
            class="geo-panel__body"
          >
            <button
              v-for="state in filteredStates"
              :key="state.id"
              type="button"
              class="geo-row"
              :class="{ 'geo-row--active': selectedStateId === state.id }"
              @click="selectState(state)"
            >
              <div class="geo-row__main">
                <div class="geo-row__title">
                  {{ state.name }}
                </div>
                <div class="geo-row__meta">
                  Code: {{ state.code || '—' }}
                </div>
              </div>

              <VChip
                size="small"
                variant="tonal"
                class="me-2"
              >
                {{ cityCount(state) }} {{ cityCount(state) === 1 ? 'city' : 'cities' }}
              </VChip>

              <VBtn
                icon
                size="x-small"
                variant="text"
                color="primary"
                aria-label="Edit state"
                @click.stop="editState(state)"
              >
                <VIcon
                  icon="bx-pencil"
                  size="18"
                />
              </VBtn>

              <VBtn
                icon
                size="x-small"
                variant="text"
                color="error"
                aria-label="Delete state"
                @click.stop="askDeleteState(state.id)"
              >
                <VIcon
                  icon="bx-trash"
                  size="18"
                />
              </VBtn>
            </button>
          </div>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard class="geo-panel">
          <VCardText class="pb-2">
            <div class="d-flex align-center justify-space-between mb-3">
              <div class="text-h6">
                Cities
                <span
                  v-if="selectedState"
                  class="text-medium-emphasis"
                >
                  ({{ filteredCities.length }})
                </span>
              </div>
              <VBtn
                icon
                size="small"
                color="success"
                rounded="circle"
                :disabled="!selectedState"
                aria-label="Create city"
                @click="openCreateCity"
              >
                <VIcon icon="bx-plus" />
              </VBtn>
            </div>

            <BaseInput
              v-model="citySearch"
              placeholder="Search cities..."
              prepend-inner-icon="bx-search"
              size="small"
              hide-details
              :disabled="!selectedState"
              class="mb-2"
            />
          </VCardText>

          <VDivider />

          <div
            v-if="!selectedState"
            class="geo-panel__body d-flex align-center justify-center"
          >
            <EmptyState title="Select a state to view its cities" />
          </div>

          <div
            v-else-if="filteredCities.length === 0"
            class="geo-panel__body d-flex align-center justify-center"
          >
            <EmptyState
              :title="(selectedState.cities || []).length ? 'No cities match your search' : 'No cities for this state'"
              :description="(selectedState.cities || []).length ? '' : 'Use + to add a city.'"
            />
          </div>

          <div
            v-else
            class="geo-panel__body"
          >
            <div
              v-for="city in filteredCities"
              :key="city.id"
              class="geo-row geo-row--static"
            >
              <div class="geo-row__main">
                <div class="geo-row__title">
                  {{ city.name }}
                </div>
                <div class="geo-row__meta">
                  Code: {{ city.code || '—' }}
                  <span v-if="city.zip"> · Zip: {{ city.zip }}</span>
                </div>
              </div>

              <VBtn
                icon
                size="x-small"
                variant="text"
                color="primary"
                aria-label="Edit city"
                @click="editCity(city)"
              >
                <VIcon
                  icon="bx-pencil"
                  size="18"
                />
              </VBtn>

              <VBtn
                icon
                size="x-small"
                variant="text"
                color="error"
                aria-label="Delete city"
                @click="askDeleteCity(city.id)"
              >
                <VIcon
                  icon="bx-trash"
                  size="18"
                />
              </VBtn>
            </div>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <VDialog
      v-model="countryDialogOpen"
      max-width="480"
      persistent
    >
      <VCard>
        <VCardTitle>{{ editingId ? 'Edit country' : 'Create country' }}</VCardTitle>
        <VCardText>
          <AppAlert :errors="countries.errors" />
          <VForm @submit.prevent="submitCountry">
            <BaseInput
              v-model="form.name"
              label="Name"
              class="mb-2"
              :error="countries.errors.name"
            />
            <BaseInput
              v-model="form.code"
              label="Code"
              class="mb-2"
              :error="countries.errors.code"
            />
            <BaseInput
              v-model="form.lat"
              label="Latitude"
              type="number"
              class="mb-2"
              :error="countries.errors.lat"
            />
            <BaseInput
              v-model="form.lng"
              label="Longitude"
              type="number"
              class="mb-2"
              :error="countries.errors.lng"
            />
          </VForm>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <BaseButton
            variant="tonal"
            label="Cancel"
            @click="cancelCountryDialog"
          />
          <BaseButton
            :label="editingId ? 'Update country' : 'Create country'"
            :loading="countries.loading"
            @click="submitCountry"
          />
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="stateDialogOpen"
      max-width="480"
      persistent
    >
      <VCard>
        <VCardTitle>
          {{ editingStateId ? 'Edit state' : `Add state${selectedCountry ? ` — ${selectedCountry.name}` : ''}` }}
        </VCardTitle>
        <VCardText>
          <AppAlert :errors="countries.stateErrors" />
          <VForm @submit.prevent="submitState">
            <BaseInput
              v-model="stateForm.name"
              label="State name"
              class="mb-2"
              :error="countries.stateErrors.name"
            />
            <BaseInput
              v-model="stateForm.code"
              label="State code"
              class="mb-2"
              :error="countries.stateErrors.code"
            />
            <BaseInput
              v-model="stateForm.lat"
              label="Latitude"
              type="number"
              class="mb-2"
              :error="countries.stateErrors.lat"
            />
            <BaseInput
              v-model="stateForm.lng"
              label="Longitude"
              type="number"
              class="mb-2"
              :error="countries.stateErrors.lng"
            />
          </VForm>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <BaseButton
            variant="tonal"
            label="Cancel"
            @click="cancelStateDialog"
          />
          <BaseButton
            :label="editingStateId ? 'Update state' : 'Add state'"
            :loading="countries.loading"
            @click="submitState"
          />
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="cityDialogOpen"
      max-width="480"
      persistent
    >
      <VCard>
        <VCardTitle>
          {{ editingCityId ? 'Edit city' : `Add city${selectedState ? ` — ${selectedState.name}` : ''}` }}
        </VCardTitle>
        <VCardText>
          <AppAlert :errors="countries.cityErrors" />
          <VForm @submit.prevent="submitCity">
            <BaseInput
              v-model="cityForm.name"
              label="City name"
              class="mb-2"
              :error="countries.cityErrors.name"
            />
            <BaseInput
              v-model="cityForm.code"
              label="City code"
              class="mb-2"
              :error="countries.cityErrors.code"
            />
            <BaseInput
              v-model="cityForm.zip"
              label="Zip"
              type="number"
              class="mb-2"
              :error="countries.cityErrors.zip"
            />
            <BaseInput
              v-model="cityForm.contact_code"
              label="Contact code"
              class="mb-2"
              :error="countries.cityErrors.contact_code"
            />
          </VForm>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <BaseButton
            variant="tonal"
            label="Cancel"
            @click="cancelCityDialog"
          />
          <BaseButton
            :label="editingCityId ? 'Update city' : 'Add city'"
            :loading="countries.loading"
            @click="submitCity"
          />
        </VCardActions>
      </VCard>
    </VDialog>

    <ConfirmDialog
      v-model="confirmDelete.open"
      :title="deleteTitle"
      :message="deleteMessage"
      confirm-label="Delete"
      confirm-color="error"
      :loading="countries.loading"
      @confirm="onDelete"
    />
  </div>
</template>

<style scoped>
.geo-panel {
  height: min(70vh, 720px);
  display: flex;
  flex-direction: column;
}

/* Vuetify .v-card-text defaults to flex-grow, which stretches the header and
   leaves a blank gap above the list inside fixed-height panels. */
.geo-panel > .v-card-text {
  flex: 0 0 auto;
}

.geo-panel__body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
}

.geo-row {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.875rem 1rem;
  border: 0;
  border-bottom: 1px solid rgba(75, 70, 92, 0.08);
  background: transparent;
  text-align: left;
  cursor: pointer;
  color: inherit;
  font: inherit;
}

.geo-row--static {
  cursor: default;
}

.geo-row:hover {
  background: rgba(75, 70, 92, 0.04);
}

.geo-row--active {
  background: rgba(105, 108, 255, 0.08);
}

.geo-row__main {
  flex: 1;
  min-width: 0;
  margin-right: 0.75rem;
}

.geo-row__title {
  font-weight: 600;
  line-height: 1.3;
}

.geo-row__meta {
  margin-top: 0.15rem;
  font-size: 0.8125rem;
  color: rgba(75, 70, 92, 0.6);
}
</style>
