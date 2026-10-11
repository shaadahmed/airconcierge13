<script setup>
import {
  MOCK_MANAGEMENT_TYPES,
  MOCK_PAYMENT_TYPES,
  MOCK_PLATFORMS,
  MOCK_PROPERTY_PAYMENT_METHODS,
  MOCK_SYSTEM_RESOURCES,
  MOCK_SYSTEM_SETTINGS,
  PAYMENT_TYPE_CATEGORY_OPTIONS,
  SETTING_TYPE_OPTIONS,
  SYSTEM_SETTINGS_TABS,
  YES_NO_OPTIONS,
} from '@/mocks/administration'

definePageMeta({ middleware: 'auth' })

const route = useRoute()
const router = useRouter()

const usingMocks = true
const successMessage = ref('')
const activeTab = ref('settings')

const managementTypes = ref(structuredClone(MOCK_MANAGEMENT_TYPES))
const paymentTypes = ref(structuredClone(MOCK_PAYMENT_TYPES))
const resources = ref(structuredClone(MOCK_SYSTEM_RESOURCES))
const platforms = ref(structuredClone(MOCK_PLATFORMS))
const systemSettings = ref(structuredClone(MOCK_SYSTEM_SETTINGS))
const paymentMethods = ref(structuredClone(MOCK_PROPERTY_PAYMENT_METHODS))

const managementSearch = ref('')
const paymentSearch = ref('')
const resourceSearch = ref('')
const platformSearch = ref('')
const settingSearch = ref('')
const paymentMethodSearch = ref('')

const dialog = reactive({
  open: false,
  kind: null,
  editingId: null,
})

const confirmDelete = reactive({
  open: false,
  kind: null,
  id: null,
})

const managementForm = reactive({ name: '' })
const paymentTypeForm = reactive({
  title: '',
  category: '',
  subcategory: '',
})
const resourceForm = reactive({
  parent_id: 0,
  name: '',
  path: '',
  link: 1,
  order: 0,
  icon_class: '',
  description: '',
})
const platformForm = reactive({
  platform_name: '',
  hostaway_channel_id: '',
  include_in_charts: 1,
})
const settingForm = reactive({
  setting_key: '',
  type: 'text',
  setting_value: '',
  description: '',
})
const paymentMethodForm = reactive({
  title: '',
  convenience_fee: '',
  management_markup: '',
})

const formErrors = ref({})

const tabFromQuery = value => {
  const match = SYSTEM_SETTINGS_TABS.find(tab => tab.value === value)

  return match ? match.value : 'settings'
}

watch(
  () => route.query.tab,
  value => {
    activeTab.value = tabFromQuery(value)
  },
  { immediate: true },
)

watch(activeTab, value => {
  if (route.query.tab === value)
    return

  router.replace({
    path: route.path,
    query: { ...route.query, tab: value },
  })
})

const flash = message => {
  successMessage.value = message
}

const nextId = rows => {
  const max = rows.reduce((highest, row) => Math.max(highest, Number(row.id) || 0), 0)

  return max + 1
}

const parentResourceItems = computed(() => [
  { title: 'Select (top-level)', value: 0 },
  ...resources.value
    .filter(row => dialog.editingId == null || Number(row.id) !== Number(dialog.editingId))
    .map(row => ({ title: row.name, value: row.id })),
])

const filteredManagementTypes = computed(() => {
  const query = managementSearch.value.trim().toLowerCase()

  if (!query)
    return managementTypes.value

  return managementTypes.value.filter(row => String(row.name || '').toLowerCase().includes(query))
})

const filteredPaymentTypes = computed(() => {
  const query = paymentSearch.value.trim().toLowerCase()

  if (!query)
    return paymentTypes.value

  return paymentTypes.value.filter(row => {
    return [row.title, row.category, row.subcategory]
      .map(value => String(value || '').toLowerCase())
      .some(value => value.includes(query))
  })
})

const filteredResources = computed(() => {
  const query = resourceSearch.value.trim().toLowerCase()

  if (!query)
    return resources.value

  return resources.value.filter(row => {
    return [row.name, row.path, row.parent_name, row.description]
      .map(value => String(value || '').toLowerCase())
      .some(value => value.includes(query))
  })
})

const filteredPlatforms = computed(() => {
  const query = platformSearch.value.trim().toLowerCase()

  if (!query)
    return platforms.value

  return platforms.value.filter(row => {
    return [row.platform_name, row.hostaway_channel_id]
      .map(value => String(value || '').toLowerCase())
      .some(value => value.includes(query))
  })
})

const filteredSettings = computed(() => {
  const query = settingSearch.value.trim().toLowerCase()

  if (!query)
    return systemSettings.value

  return systemSettings.value.filter(row => {
    return [row.setting_key, row.setting_value, row.type, row.description]
      .map(value => String(value || '').toLowerCase())
      .some(value => value.includes(query))
  })
})

const filteredPaymentMethods = computed(() => {
  const query = paymentMethodSearch.value.trim().toLowerCase()

  if (!query)
    return paymentMethods.value

  return paymentMethods.value.filter(row => String(row.title || '').toLowerCase().includes(query))
})

const dialogTitle = computed(() => {
  const editing = dialog.editingId != null

  switch (dialog.kind) {
    case 'management-type':
      return editing ? 'Edit management type' : 'Add management type'
    case 'payment-type':
      return editing ? 'Edit payment type' : 'Add payment type'
    case 'resource':
      return editing ? 'Edit resource' : 'Add resource'
    case 'platform':
      return editing ? 'Edit platform' : 'Add platform'
    case 'setting':
      return editing ? 'Edit setting' : 'Add system setting'
    case 'payment-method':
      return editing ? 'Update payment method' : 'Add payment method'
    default:
      return ''
  }
})

const settingKeyReadonly = computed(() => dialog.kind === 'setting' && dialog.editingId != null)

const settingValueInputType = computed(() => {
  switch (settingForm.type) {
    case 'numeric':
      return 'number'
    case 'email':
      return 'email'
    case 'url':
      return 'url'
    case 'date':
      return 'date'
    case 'time':
      return 'time'
    case 'datetime':
      return 'datetime-local'
    default:
      return 'text'
  }
})

const settingValueIsTextarea = computed(() => ['json', 'array'].includes(settingForm.type))
const settingValueIsBoolean = computed(() => settingForm.type === 'boolean')

const yesNoLabel = value => (Number(value) === 1 ? 'Yes' : 'No')

const resetForms = () => {
  Object.assign(managementForm, { name: '' })
  Object.assign(paymentTypeForm, { title: '', category: '', subcategory: '' })
  Object.assign(resourceForm, {
    parent_id: 0,
    name: '',
    path: '',
    link: 1,
    order: 0,
    icon_class: '',
    description: '',
  })
  Object.assign(platformForm, {
    platform_name: '',
    hostaway_channel_id: '',
    include_in_charts: 1,
  })
  Object.assign(settingForm, {
    setting_key: '',
    type: 'text',
    setting_value: '',
    description: '',
  })
  Object.assign(paymentMethodForm, {
    title: '',
    convenience_fee: '',
    management_markup: '',
  })
  formErrors.value = {}
}

const openCreate = kind => {
  resetForms()
  dialog.kind = kind
  dialog.editingId = null
  dialog.open = true
}

const openEdit = (kind, row) => {
  resetForms()
  dialog.kind = kind
  dialog.editingId = row.id

  if (kind === 'management-type')
    Object.assign(managementForm, { name: row.name ?? '' })
  else if (kind === 'payment-type') {
    Object.assign(paymentTypeForm, {
      title: row.title ?? '',
      category: row.category ?? '',
      subcategory: row.subcategory ?? '',
    })
  }
  else if (kind === 'resource') {
    Object.assign(resourceForm, {
      parent_id: row.parent_id ?? 0,
      name: row.name ?? '',
      path: row.path ?? '',
      link: Number(row.link ?? 1),
      order: row.order ?? 0,
      icon_class: row.icon_class ?? '',
      description: row.description ?? '',
    })
  }
  else if (kind === 'platform') {
    Object.assign(platformForm, {
      platform_name: row.platform_name ?? '',
      hostaway_channel_id: row.hostaway_channel_id ?? '',
      include_in_charts: Number(row.include_in_charts ?? 1),
    })
  }
  else if (kind === 'setting') {
    Object.assign(settingForm, {
      setting_key: row.setting_key ?? '',
      type: row.type ?? 'text',
      setting_value: row.setting_value ?? '',
      description: row.description ?? '',
    })
  }
  else if (kind === 'payment-method') {
    Object.assign(paymentMethodForm, {
      title: row.title ?? '',
      convenience_fee: row.convenience_fee ?? '',
      management_markup: row.management_markup ?? '',
    })
  }

  dialog.open = true
}

const closeDialog = () => {
  dialog.open = false
  dialog.kind = null
  dialog.editingId = null
  resetForms()
}

const validateSettingKey = key => /^[a-zA-Z_]+$/.test(String(key || '').trim())

const validateCurrentForm = () => {
  const errors = {}

  if (dialog.kind === 'management-type') {
    if (!String(managementForm.name || '').trim())
      errors.name = 'Type name is required.'
  }
  else if (dialog.kind === 'payment-type') {
    if (!String(paymentTypeForm.title || '').trim())
      errors.title = 'Title is required.'
    if (!String(paymentTypeForm.category || '').trim())
      errors.category = 'Category is required.'
    if (!String(paymentTypeForm.subcategory || '').trim())
      errors.subcategory = 'Subcategory is required.'
  }
  else if (dialog.kind === 'resource') {
    if (!String(resourceForm.name || '').trim())
      errors.name = 'Name is required.'
    if (!String(resourceForm.path || '').trim())
      errors.path = 'Path is required.'
    if (resourceForm.link !== 0 && resourceForm.link !== 1)
      errors.link = 'Link is required.'
  }
  else if (dialog.kind === 'platform') {
    if (!String(platformForm.platform_name || '').trim())
      errors.platform_name = 'Platform name is required.'
    if (platformForm.include_in_charts !== 0 && platformForm.include_in_charts !== 1)
      errors.include_in_charts = 'Include in graphs/charts is required.'
  }
  else if (dialog.kind === 'setting') {
    if (!settingKeyReadonly.value) {
      if (!String(settingForm.setting_key || '').trim())
        errors.setting_key = 'Setting name is required.'
      else if (!validateSettingKey(settingForm.setting_key))
        errors.setting_key = 'Setting key can only contain letters (a-z, A-Z) and underscores (_).'
    }
    if (!String(settingForm.type || '').trim())
      errors.type = 'Type is required.'
    if (settingForm.setting_value === '' || settingForm.setting_value === null || settingForm.setting_value === undefined)
      errors.setting_value = 'Setting value is required.'
    if (!String(settingForm.description || '').trim())
      errors.description = 'Description is required.'
  }
  else if (dialog.kind === 'payment-method') {
    if (!String(paymentMethodForm.title || '').trim())
      errors.title = 'Payment type name is required.'
    if (paymentMethodForm.convenience_fee === '' || paymentMethodForm.convenience_fee === null)
      errors.convenience_fee = 'Convenience fee is required.'
    if (paymentMethodForm.management_markup === '' || paymentMethodForm.management_markup === null)
      errors.management_markup = 'Administrative fee is required.'
  }

  formErrors.value = errors

  return Object.keys(errors).length === 0
}

const parentNameFor = parentId => {
  if (!parentId || Number(parentId) === 0)
    return '—'

  const parent = resources.value.find(row => Number(row.id) === Number(parentId))

  return parent?.name || '—'
}

const submitDialog = () => {
  if (!validateCurrentForm())
    return

  if (dialog.kind === 'management-type') {
    const payload = { name: managementForm.name.trim() }

    if (dialog.editingId != null) {
      const row = managementTypes.value.find(item => item.id === dialog.editingId)
      if (row)
        Object.assign(row, payload)
      flash('Management type updated.')
    }
    else {
      managementTypes.value.push({ id: nextId(managementTypes.value), ...payload })
      flash('Management type created.')
    }
  }
  else if (dialog.kind === 'payment-type') {
    const payload = {
      title: paymentTypeForm.title.trim(),
      category: paymentTypeForm.category,
      subcategory: paymentTypeForm.subcategory.trim(),
    }

    if (dialog.editingId != null) {
      const row = paymentTypes.value.find(item => item.id === dialog.editingId)
      if (row)
        Object.assign(row, payload)
      flash('Payment type updated.')
    }
    else {
      paymentTypes.value.push({ id: nextId(paymentTypes.value), ...payload })
      flash('Payment type created.')
    }
  }
  else if (dialog.kind === 'resource') {
    const payload = {
      parent_id: Number(resourceForm.parent_id) || 0,
      parent_name: parentNameFor(resourceForm.parent_id),
      name: resourceForm.name.trim(),
      path: resourceForm.path.trim(),
      link: Number(resourceForm.link),
      order: Number(resourceForm.order) || 0,
      icon_class: resourceForm.icon_class.trim(),
      description: resourceForm.description.trim(),
    }

    if (dialog.editingId != null) {
      const row = resources.value.find(item => item.id === dialog.editingId)
      if (row)
        Object.assign(row, payload)
      flash('Resource updated.')
    }
    else {
      resources.value.push({ id: nextId(resources.value), ...payload })
      flash('Resource created.')
    }
  }
  else if (dialog.kind === 'platform') {
    const payload = {
      platform_name: platformForm.platform_name.trim(),
      hostaway_channel_id: String(platformForm.hostaway_channel_id || '').trim(),
      include_in_charts: Number(platformForm.include_in_charts),
    }

    if (dialog.editingId != null) {
      const row = platforms.value.find(item => item.id === dialog.editingId)
      if (row)
        Object.assign(row, payload)
      flash('Platform updated.')
    }
    else {
      platforms.value.push({ id: nextId(platforms.value), ...payload })
      flash('Platform created.')
    }
  }
  else if (dialog.kind === 'setting') {
    const payload = {
      setting_key: settingForm.setting_key.trim(),
      type: settingForm.type,
      setting_value: String(settingForm.setting_value),
      description: settingForm.description.trim(),
    }

    if (dialog.editingId != null) {
      const row = systemSettings.value.find(item => item.id === dialog.editingId)
      if (row) {
        Object.assign(row, {
          setting_value: payload.setting_value,
          description: payload.description,
        })
      }
      flash('System setting updated.')
    }
    else {
      systemSettings.value.push({ id: nextId(systemSettings.value), ...payload })
      flash('System setting created.')
    }
  }
  else if (dialog.kind === 'payment-method') {
    const payload = {
      title: paymentMethodForm.title.trim(),
      convenience_fee: String(paymentMethodForm.convenience_fee),
      management_markup: String(paymentMethodForm.management_markup),
    }

    if (dialog.editingId != null) {
      const row = paymentMethods.value.find(item => item.id === dialog.editingId)
      if (row)
        Object.assign(row, payload)
      flash('Payment method updated.')
    }
    else {
      paymentMethods.value.push({ id: nextId(paymentMethods.value), ...payload })
      flash('Payment method created.')
    }
  }

  closeDialog()
}

const askDelete = (kind, id) => {
  confirmDelete.open = true
  confirmDelete.kind = kind
  confirmDelete.id = id
}

const removeRecord = () => {
  const { kind, id } = confirmDelete

  if (kind === 'management-type')
    managementTypes.value = managementTypes.value.filter(row => row.id !== id)
  else if (kind === 'payment-type')
    paymentTypes.value = paymentTypes.value.filter(row => row.id !== id)
  else if (kind === 'resource')
    resources.value = resources.value.filter(row => row.id !== id)
  else if (kind === 'platform')
    platforms.value = platforms.value.filter(row => row.id !== id)
  else if (kind === 'setting')
    systemSettings.value = systemSettings.value.filter(row => row.id !== id)
  else if (kind === 'payment-method')
    paymentMethods.value = paymentMethods.value.filter(row => row.id !== id)

  confirmDelete.open = false
  confirmDelete.kind = null
  confirmDelete.id = null
  flash('Record deleted.')
}

watch(() => settingForm.type, (type, previous) => {
  if (dialog.kind !== 'setting' || settingKeyReadonly.value)
    return

  if (type === previous)
    return

  if (type === 'boolean' && settingForm.setting_value !== '0' && settingForm.setting_value !== '1')
    settingForm.setting_value = ''
})
</script>

<template>
  <div>
    <PageHeader
      title="System Settings"
      subtitle="Management types, payment types, resources, platforms, and configuration"
    />

    <VAlert
      v-if="usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Showing UI preview data until the administration APIs are connected.
    </VAlert>

    <VAlert
      v-if="successMessage"
      type="success"
      variant="tonal"
      density="compact"
      closable
      class="mb-4"
      @click:close="successMessage = ''"
    >
      {{ successMessage }}
    </VAlert>

    <VCard>
      <VTabs
        v-model="activeTab"
        show-arrows
      >
        <VTab
          v-for="tab in SYSTEM_SETTINGS_TABS"
          :key="tab.value"
          :value="tab.value"
        >
          {{ tab.title }}
        </VTab>
      </VTabs>

      <VDivider />

      <VTabsWindow v-model="activeTab">
        <!-- Management Types -->
        <VTabsWindowItem value="management-types">
          <VCardText>
            <DataTableShell
              title="Types list"
              :empty="filteredManagementTypes.length === 0"
              :empty-colspan="3"
              empty-title="No management types found"
            >
              <template #actions>
                <BaseButton
                  label="Add New Management Type"
                  prepend-icon="bx-plus"
                  @click="openCreate('management-type')"
                />
              </template>
              <template #toolbar>
                <BaseInput
                  v-model="managementSearch"
                  placeholder="Search types..."
                  prepend-inner-icon="bx-search"
                  size="small"
                  hide-details
                />
              </template>
              <template #head>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th class="text-end">
                      Actions
                    </th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="row in filteredManagementTypes"
                :key="row.id"
              >
                <td>{{ row.id }}</td>
                <td>{{ row.name }}</td>
                <td class="text-end text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="openEdit('management-type', row)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="askDelete('management-type', row.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCardText>
        </VTabsWindowItem>

        <!-- Payment Types -->
        <VTabsWindowItem value="payment-types">
          <VCardText>
            <DataTableShell
              title="Types list"
              :empty="filteredPaymentTypes.length === 0"
              :empty-colspan="5"
              empty-title="No payment types found"
            >
              <template #actions>
                <BaseButton
                  label="Add New Payment Type"
                  prepend-icon="bx-plus"
                  @click="openCreate('payment-type')"
                />
              </template>
              <template #toolbar>
                <BaseInput
                  v-model="paymentSearch"
                  placeholder="Search title, category, subcategory..."
                  prepend-inner-icon="bx-search"
                  size="small"
                  hide-details
                />
              </template>
              <template #head>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th class="text-end">
                      Actions
                    </th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="(row, index) in filteredPaymentTypes"
                :key="row.id"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ row.title }}</td>
                <td>{{ row.category }}</td>
                <td>{{ row.subcategory }}</td>
                <td class="text-end text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="openEdit('payment-type', row)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="askDelete('payment-type', row.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCardText>
        </VTabsWindowItem>

        <!-- System Resources -->
        <VTabsWindowItem value="resources">
          <VCardText>
            <DataTableShell
              title="System resource list"
              :empty="filteredResources.length === 0"
              :empty-colspan="7"
              empty-title="No system resources found"
            >
              <template #actions>
                <BaseButton
                  label="Add New Resource"
                  prepend-icon="bx-plus"
                  @click="openCreate('resource')"
                />
              </template>
              <template #toolbar>
                <BaseInput
                  v-model="resourceSearch"
                  placeholder="Search resources..."
                  prepend-inner-icon="bx-search"
                  size="small"
                  hide-details
                />
              </template>
              <template #head>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Parent</th>
                    <th>Name</th>
                    <th>Path</th>
                    <th>Description</th>
                    <th class="text-center">
                      Order
                    </th>
                    <th class="text-end">
                      Actions
                    </th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="row in filteredResources"
                :key="row.id"
              >
                <td>{{ row.id }}</td>
                <td>{{ row.parent_name || '—' }}</td>
                <td>{{ row.name }}</td>
                <td>{{ row.path }}</td>
                <td>{{ row.description || '—' }}</td>
                <td class="text-center">
                  {{ row.order }}
                </td>
                <td class="text-end text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="openEdit('resource', row)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="askDelete('resource', row.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCardText>
        </VTabsWindowItem>

        <!-- Platforms -->
        <VTabsWindowItem value="platforms">
          <VCardText>
            <DataTableShell
              title="Platforms"
              :empty="filteredPlatforms.length === 0"
              :empty-colspan="5"
              empty-title="No platforms found"
            >
              <template #actions>
                <BaseButton
                  label="Add New Platform"
                  prepend-icon="bx-plus"
                  @click="openCreate('platform')"
                />
              </template>
              <template #toolbar>
                <BaseInput
                  v-model="platformSearch"
                  placeholder="Search platforms..."
                  prepend-inner-icon="bx-search"
                  size="small"
                  hide-details
                />
              </template>
              <template #head>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Platform name</th>
                    <th>Hostaway channel ID</th>
                    <th class="text-center">
                      Included in graphs/charts
                    </th>
                    <th class="text-end">
                      Actions
                    </th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="row in filteredPlatforms"
                :key="row.id"
              >
                <td>{{ row.id }}</td>
                <td>{{ row.platform_name }}</td>
                <td>{{ row.hostaway_channel_id || '—' }}</td>
                <td class="text-center">
                  {{ yesNoLabel(row.include_in_charts) }}
                </td>
                <td class="text-end text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="openEdit('platform', row)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="askDelete('platform', row.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCardText>
        </VTabsWindowItem>

        <!-- Payment Methods -->
        <VTabsWindowItem value="payment-methods">
          <VCardText>
            <DataTableShell
              title="Payment methods"
              :empty="filteredPaymentMethods.length === 0"
              :empty-colspan="5"
              empty-title="No payment methods found"
            >
              <template #toolbar>
                <BaseInput
                  v-model="paymentMethodSearch"
                  placeholder="Search payment methods..."
                  prepend-inner-icon="bx-search"
                  size="small"
                  hide-details
                />
              </template>
              <template #head>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Convenience fee ($)</th>
                    <th>Administrative fee (%)</th>
                    <th class="text-end">
                      Actions
                    </th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="row in filteredPaymentMethods"
                :key="row.id"
              >
                <td>{{ row.id }}</td>
                <td>{{ row.title }}</td>
                <td>{{ row.convenience_fee }}</td>
                <td>{{ row.management_markup }}</td>
                <td class="text-end text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="openEdit('payment-method', row)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="askDelete('payment-method', row.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCardText>
        </VTabsWindowItem>

        <!-- Settings -->
        <VTabsWindowItem value="settings">
          <VCardText>
            <DataTableShell
              title="System settings list"
              :empty="filteredSettings.length === 0"
              :empty-colspan="6"
              empty-title="No system settings found"
            >
              <template #actions>
                <BaseButton
                  label="Add New System Setting"
                  prepend-icon="bx-plus"
                  @click="openCreate('setting')"
                />
              </template>
              <template #toolbar>
                <BaseInput
                  v-model="settingSearch"
                  placeholder="Search settings..."
                  prepend-inner-icon="bx-search"
                  size="small"
                  hide-details
                />
              </template>
              <template #head>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Setting name</th>
                    <th>Setting value</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="text-end">
                      Actions
                    </th>
                  </tr>
                </thead>
              </template>
              <tr
                v-for="row in filteredSettings"
                :key="row.id"
              >
                <td>{{ row.id }}</td>
                <td>{{ row.setting_key }}</td>
                <td
                  class="text-truncate"
                  style="max-width: 220px;"
                >
                  {{ row.type === 'boolean' ? yesNoLabel(row.setting_value) : row.setting_value }}
                </td>
                <td>{{ row.type }}</td>
                <td>{{ row.description }}</td>
                <td class="text-end text-no-wrap">
                  <BaseButton
                    size="small"
                    variant="tonal"
                    label="Edit"
                    class="me-2"
                    @click="openEdit('setting', row)"
                  />
                  <BaseButton
                    size="small"
                    variant="tonal"
                    color="error"
                    label="Delete"
                    @click="askDelete('setting', row.id)"
                  />
                </td>
              </tr>
            </DataTableShell>
          </VCardText>
        </VTabsWindowItem>
      </VTabsWindow>
    </VCard>

    <!-- Shared create / edit dialog -->
    <VDialog
      v-model="dialog.open"
      :max-width="dialog.kind === 'resource' ? 720 : 520"
      persistent
      scrollable
    >
      <VCard>
        <VCardTitle>{{ dialogTitle }}</VCardTitle>
        <VCardText>
          <AppAlert :errors="formErrors" />

          <VForm @submit.prevent="submitDialog">
            <!-- Management type -->
            <template v-if="dialog.kind === 'management-type'">
              <BaseInput
                v-model="managementForm.name"
                label="Type name"
                required
                :error="formErrors.name"
              />
            </template>

            <!-- Payment type -->
            <template v-else-if="dialog.kind === 'payment-type'">
              <BaseInput
                v-model="paymentTypeForm.title"
                label="Title"
                required
                class="mb-3"
                :error="formErrors.title"
              />
              <BaseSelect
                v-model="paymentTypeForm.category"
                label="Category"
                :items="PAYMENT_TYPE_CATEGORY_OPTIONS"
                required
                class="mb-3"
                :error="formErrors.category"
              />
              <BaseInput
                v-model="paymentTypeForm.subcategory"
                label="Subcategory"
                required
                :error="formErrors.subcategory"
              />
            </template>

            <!-- Resource -->
            <template v-else-if="dialog.kind === 'resource'">
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <BaseSelect
                    v-model="resourceForm.parent_id"
                    label="Parent resource"
                    :items="parentResourceItems"
                    class="mb-3"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <BaseInput
                    v-model="resourceForm.name"
                    label="Name"
                    required
                    class="mb-3"
                    :error="formErrors.name"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <BaseInput
                    v-model="resourceForm.path"
                    label="Path"
                    required
                    class="mb-3"
                    :error="formErrors.path"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <BaseSelect
                    v-model="resourceForm.link"
                    label="Link"
                    :items="YES_NO_OPTIONS"
                    required
                    class="mb-3"
                    :error="formErrors.link"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <BaseInput
                    v-model="resourceForm.order"
                    label="Order"
                    type="number"
                    class="mb-3"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <BaseTextarea
                    v-model="resourceForm.icon_class"
                    label="Icon class"
                    hint="e.g. fa fa-database"
                    persistent-hint
                    class="mb-3"
                    rows="2"
                  />
                </VCol>
                <VCol cols="12">
                  <BaseTextarea
                    v-model="resourceForm.description"
                    label="Description"
                    rows="3"
                  />
                </VCol>
              </VRow>
            </template>

            <!-- Platform -->
            <template v-else-if="dialog.kind === 'platform'">
              <BaseInput
                v-model="platformForm.platform_name"
                label="Platform name"
                required
                class="mb-3"
                :error="formErrors.platform_name"
              />
              <BaseInput
                v-model="platformForm.hostaway_channel_id"
                label="Hostaway channel ID"
                class="mb-3"
              />
              <BaseSelect
                v-model="platformForm.include_in_charts"
                label="Include in graphs/charts"
                :items="YES_NO_OPTIONS"
                required
                :error="formErrors.include_in_charts"
              />
            </template>

            <!-- System setting -->
            <template v-else-if="dialog.kind === 'setting'">
              <BaseInput
                v-model="settingForm.setting_key"
                label="Setting name"
                required
                class="mb-3"
                :readonly="settingKeyReadonly"
                :error="formErrors.setting_key"
                hint="Letters and underscores only"
                persistent-hint
              />
              <BaseSelect
                v-model="settingForm.type"
                label="Type"
                :items="SETTING_TYPE_OPTIONS"
                required
                class="mb-3"
                :readonly="settingKeyReadonly"
                :disabled="settingKeyReadonly"
                :error="formErrors.type"
              />
              <BaseSelect
                v-if="settingValueIsBoolean"
                v-model="settingForm.setting_value"
                label="Setting value"
                :items="[
                  { title: 'Yes', value: '1' },
                  { title: 'No', value: '0' },
                ]"
                required
                class="mb-3"
                :error="formErrors.setting_value"
              />
              <BaseTextarea
                v-else-if="settingValueIsTextarea"
                v-model="settingForm.setting_value"
                label="Setting value"
                required
                rows="4"
                class="mb-3"
                placeholder="Enter JSON or array format"
                :error="formErrors.setting_value"
              />
              <BaseInput
                v-else
                v-model="settingForm.setting_value"
                label="Setting value"
                required
                class="mb-3"
                :type="settingValueInputType"
                :error="formErrors.setting_value"
              />
              <BaseInput
                v-model="settingForm.description"
                label="Description"
                required
                :error="formErrors.description"
              />
            </template>

            <!-- Payment method -->
            <template v-else-if="dialog.kind === 'payment-method'">
              <BaseInput
                v-model="paymentMethodForm.title"
                label="Payment type name"
                required
                class="mb-3"
                :error="formErrors.title"
              />
              <BaseInput
                v-model="paymentMethodForm.convenience_fee"
                label="Convenience fee ($)"
                required
                class="mb-3"
                :error="formErrors.convenience_fee"
              />
              <BaseInput
                v-model="paymentMethodForm.management_markup"
                label="Administrative fee (%)"
                required
                :error="formErrors.management_markup"
              />
            </template>
          </VForm>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <BaseButton
            variant="tonal"
            label="Close"
            @click="closeDialog"
          />
          <BaseButton
            color="success"
            label="Save"
            prepend-icon="bx-check"
            @click="submitDialog"
          />
        </VCardActions>
      </VCard>
    </VDialog>

    <ConfirmDialog
      v-model="confirmDelete.open"
      title="Delete record?"
      message="This action cannot be undone."
      confirm-label="Delete"
      confirm-color="error"
      @confirm="removeRecord"
    />
  </div>
</template>
