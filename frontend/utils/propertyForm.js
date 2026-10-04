/**
 * Property create/edit form helpers.
 * Field set mirrors legacy admin property create + edit screens.
 */

export const createEmptyPropertyForm = () => ({
  id: null,
  owners: [],
  region_id: null,
  subregion_id: null,
  management_type_id: 1,
  hostaway_listing_id: '',
  property_title: '',
  email_titles: [''],
  street_address: '',
  city: '',
  state: null,
  zipcode: '',
  contract_start_date: new Date().toISOString().slice(0, 10),
  contract_end_date: '',
  contract_end_reason: null,
  supportemail: '',
  bedrooms: null,
  bathrooms: null,
  ac_management_fee: 22,
  exit_cleaning_fee: '',
  cleaners: [],
  tot_mode: null,
  tot_method: null,
  vrbo_tot_mode: null,
  vrbo_tot_method: null,
  status: 1,
  payment_method: null,
  airconcierge_pays_cleaners: null,
  property_cohost: null,
  parent_id: null,
  primary_residence: false,
  secondary_residence: false,
  property_code: '',
  owners_montly_cost: '',
  apply_resort_fee: false,
  management_notes: '',
  property_image_url: '',
})

const asStatus = value => {
  if (value === true || value === 1 || value === '1')
    return 1
  if (value === 2 || value === '2')
    return 2

  return 0
}

const asYesNo = value => {
  if (value === true || value === 1 || value === '1')
    return 1
  if (value === false || value === 0 || value === '0')
    return 0

  return null
}

const asNullableNumber = value => {
  if (value === null || value === undefined || value === '')
    return null

  const number = Number(value)

  return Number.isNaN(number) ? null : number
}

export const mapPropertyToForm = property => {
  const emailTitles = Array.isArray(property?.email_titles) && property.email_titles.length
    ? property.email_titles.map(title => title || '')
    : [property?.email_title || '']

  return {
    ...createEmptyPropertyForm(),
    id: property?.id ?? null,
    owners: Array.isArray(property?.owners)
      ? property.owners.map(owner => (typeof owner === 'object' ? owner.id : owner)).filter(Boolean)
      : (property?.owner_ids || []),
    region_id: asNullableNumber(property?.region_id),
    subregion_id: asNullableNumber(property?.subregion_id),
    management_type_id: asNullableNumber(property?.management_type_id) ?? 1,
    hostaway_listing_id: property?.hostaway_listing_id ?? '',
    property_title: property?.property_title || '',
    email_titles: emailTitles.length ? emailTitles : [''],
    street_address: property?.street_address || '',
    city: property?.city || '',
    state: property?.state || null,
    zipcode: property?.zipcode || '',
    contract_start_date: property?.contract_start_date || '',
    contract_end_date: property?.contract_end_date || '',
    contract_end_reason: property?.contract_end_reason || null,
    supportemail: property?.supportemail || '',
    bedrooms: asNullableNumber(property?.bedrooms),
    bathrooms: asNullableNumber(property?.bathrooms),
    ac_management_fee: asNullableNumber(property?.ac_management_fee) ?? 22,
    exit_cleaning_fee: property?.exit_cleaning_fee ?? '',
    cleaners: Array.isArray(property?.cleaners)
      ? property.cleaners.map(cleaner => (typeof cleaner === 'object' ? cleaner.id : cleaner)).filter(Boolean)
      : (property?.cleaner_ids || []),
    tot_mode: property?.tot_mode || null,
    tot_method: property?.tot_method || null,
    vrbo_tot_mode: asNullableNumber(property?.vrbo_tot_mode),
    vrbo_tot_method: property?.vrbo_tot_method || null,
    status: asStatus(property?.status),
    payment_method: property?.payment_method || null,
    airconcierge_pays_cleaners: asYesNo(property?.airconcierge_pays_cleaners),
    property_cohost: asYesNo(property?.property_cohost),
    parent_id: asNullableNumber(property?.parent_id),
    primary_residence: Boolean(property?.primary_residence),
    secondary_residence: Boolean(property?.secondary_residence),
    property_code: property?.property_code || '',
    owners_montly_cost: property?.owners_montly_cost ?? '',
    apply_resort_fee: Boolean(property?.apply_resort_fee),
    management_notes: property?.management_notes || '',
    property_image_url: property?.property_image_url || '',
  }
}

export const serializePropertyForm = form => {
  const emailTitles = (form.email_titles || [])
    .map(title => String(title || '').trim())
    .filter(Boolean)

  return {
    owners: form.owners || [],
    region_id: form.region_id || null,
    subregion_id: form.subregion_id || null,
    management_type_id: form.management_type_id || null,
    hostaway_listing_id: form.hostaway_listing_id || null,
    property_title: form.property_title || null,
    email_title: emailTitles[0] || null,
    email_titles: emailTitles,
    street_address: form.street_address || null,
    city: form.city || null,
    state: form.state || null,
    zipcode: form.zipcode || null,
    contract_start_date: form.contract_start_date || null,
    contract_end_date: form.contract_end_date || null,
    contract_end_reason: form.contract_end_reason || null,
    supportemail: form.supportemail || null,
    bedrooms: form.bedrooms === '' || form.bedrooms === null ? null : Number(form.bedrooms),
    bathrooms: form.bathrooms === '' || form.bathrooms === null ? null : Number(form.bathrooms),
    ac_management_fee: form.ac_management_fee === '' || form.ac_management_fee === null
      ? null
      : Number(form.ac_management_fee),
    exit_cleaning_fee: form.exit_cleaning_fee === '' || form.exit_cleaning_fee === null
      ? null
      : Number(form.exit_cleaning_fee),
    cleaners: form.cleaners || [],
    tot_mode: form.tot_mode || null,
    tot_method: form.tot_method || null,
    vrbo_tot_mode: form.vrbo_tot_mode === '' || form.vrbo_tot_mode === null
      ? null
      : Number(form.vrbo_tot_mode),
    vrbo_tot_method: form.vrbo_tot_method || null,
    status: form.status === '' || form.status === null ? null : Number(form.status),
    payment_method: form.payment_method || null,
    airconcierge_pays_cleaners: form.airconcierge_pays_cleaners === '' || form.airconcierge_pays_cleaners === null
      ? null
      : Number(form.airconcierge_pays_cleaners),
    property_cohost: form.property_cohost === '' || form.property_cohost === null
      ? null
      : Number(form.property_cohost),
    parent_id: form.parent_id || null,
    primary_residence: Boolean(form.primary_residence),
    secondary_residence: Boolean(form.secondary_residence),
    property_code: form.property_code || null,
    owners_montly_cost: form.owners_montly_cost === '' || form.owners_montly_cost === null
      ? null
      : Number(form.owners_montly_cost),
    apply_resort_fee: Boolean(form.apply_resort_fee),
    management_notes: form.management_notes || null,
    property_image_url: form.property_image_url || null,
  }
}
