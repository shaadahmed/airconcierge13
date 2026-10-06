import { findCityById, MOCK_COUNTRIES } from '@/mocks/countries'
import { MOCK_CLEANERS } from '@/mocks/cleaners'
import { MOCK_OWNERS } from '@/mocks/owners'
import { MOCK_PROPERTIES } from '@/mocks/properties'
import { MOCK_REGIONS } from '@/mocks/regions'

/**
 * Shared select options for property create/edit.
 * Prefers live store data when present; falls back to UI mocks.
 */
export const usePropertyFormOptions = (formRef = null) => {
  const properties = usePropertiesStore()
  const owners = useOwnersStore()
  const regions = useRegionsStore()
  const countries = useCountriesStore()

  const usingMocks = ref(false)

  const normalizeList = payload => {
    const data = payload?.data || payload || []

    return Array.isArray(data) ? data : []
  }

  const loadLookups = async () => {
    const tasks = [
      properties.load().catch(() => null),
      owners.load().catch(() => null),
      regions.load().catch(() => null),
      countries.load().catch(() => null),
    ]

    await Promise.all(tasks)

    const hasOwners = normalizeList(owners.data).length > 0
    const hasRegions = normalizeList(regions.data).length > 0
    const hasProperties = normalizeList(properties.data).length > 0
    const hasCountries = normalizeList(countries.data).length > 0

    usingMocks.value = !(hasOwners || hasRegions || hasProperties || hasCountries)
  }

  const currentForm = () => formRef?.value ?? formRef ?? {}

  const countryList = computed(() => {
    const list = normalizeList(countries.data)

    return list.length ? list : MOCK_COUNTRIES
  })

  const ownerOptions = computed(() => {
    const list = normalizeList(owners.data)
    const source = list.length ? list : MOCK_OWNERS

    return source.map(owner => ({
      title: owner.full_name || [owner.first_name, owner.last_name].filter(Boolean).join(' ') || `Owner #${owner.id}`,
      value: owner.id,
    }))
  })

  const regionOptions = computed(() => {
    const list = normalizeList(regions.data)
    const source = list.length ? list : MOCK_REGIONS

    return source.map(region => ({
      title: region.region_name || region.name || `Region #${region.id}`,
      value: region.id,
      subregions: region.subregions || [],
    }))
  })

  const subregionOptions = computed(() => {
    const regionId = currentForm().region_id
    if (!regionId)
      return []

    const region = regionOptions.value.find(item => Number(item.value) === Number(regionId))
    const subregions = region?.subregions || []

    if (subregions.length) {
      return subregions.map(subregion => ({
        title: subregion.name || `Subregion #${subregion.id}`,
        value: subregion.id,
      }))
    }

    // Fallback: derive from loaded/mock properties when region payload has no nested subregions.
    const propertyList = normalizeList(properties.data)
    const source = propertyList.length ? propertyList : MOCK_PROPERTIES
    const map = new Map()

    for (const property of source) {
      if (Number(property.region_id) !== Number(regionId) || property.subregion_id == null)
        continue

      if (!map.has(property.subregion_id)) {
        map.set(property.subregion_id, {
          title: property.subregion?.name || `Subregion #${property.subregion_id}`,
          value: property.subregion_id,
        })
      }
    }

    return [...map.values()]
  })

  const countryOptions = computed(() => countryList.value.map(country => ({
    title: country.name || `Country #${country.id}`,
    value: country.id,
  })))

  const stateOptions = computed(() => {
    const countryId = currentForm().country_id
    if (!countryId)
      return []

    const country = countryList.value.find(item => Number(item.id) === Number(countryId))

    return (country?.states || []).map(state => ({
      title: state.name || `State #${state.id}`,
      value: state.id,
    }))
  })

  const cityOptions = computed(() => {
    const countryId = currentForm().country_id
    const stateId = currentForm().state_id
    if (!countryId || !stateId)
      return []

    const country = countryList.value.find(item => Number(item.id) === Number(countryId))
    const state = (country?.states || []).find(item => Number(item.id) === Number(stateId))

    return (state?.cities || []).map(city => ({
      title: city.name || `City #${city.id}`,
      value: city.id,
      zip: city.zip ?? null,
    }))
  })

  const resolveCity = cityId => findCityById(cityId, countryList.value)

  const cleanerOptions = computed(() => {
    // Cleaners API is not wired yet — mock list for form preview.
    return MOCK_CLEANERS.map(cleaner => ({
      title: cleaner.company ? `${cleaner.full_name} (${cleaner.company})` : cleaner.full_name,
      value: cleaner.id,
    }))
  })

  const parentPropertyOptions = computed(() => {
    const propertyList = normalizeList(properties.data)
    const source = propertyList.length ? propertyList : MOCK_PROPERTIES
    const currentId = currentForm().id ?? null

    return [
      { title: 'This is a parent property', value: null },
      ...source
        .filter(property => !currentId || Number(property.id) !== Number(currentId))
        .map(property => ({
          title: property.property_title || `Property #${property.id}`,
          value: property.id,
        })),
    ]
  })

  return {
    usingMocks,
    loadLookups,
    ownerOptions,
    regionOptions,
    subregionOptions,
    countryOptions,
    stateOptions,
    cityOptions,
    resolveCity,
    cleanerOptions,
    parentPropertyOptions,
  }
}
