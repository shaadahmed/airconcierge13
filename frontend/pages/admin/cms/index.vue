<script setup>
/* eslint-disable vue/no-v-html -- agreement HTML comes from trusted Laravel CMS content / UI preview fixtures */
const cms = useCmsStore()

const PAGE_ID = 'owner-agreement'

const form = reactive({
  content: '',
})

const previewOpen = ref(false)
const successMessage = ref('')

const syncFormFromStore = () => {
  form.content = cms.data?.content ?? ''
}

onMounted(async () => {
  await cms.load(PAGE_ID)
  syncFormFromStore()
})

const openPreview = () => {
  previewOpen.value = true
}

const submit = async () => {
  successMessage.value = ''
  try {
    await cms.save(PAGE_ID, form.content)
    syncFormFromStore()
    successMessage.value = 'Content updated successfully!'
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
      title="Content Management"
      subtitle="Edit the owner agreement policy shown to owners"
    />

    <VAlert
      v-if="cms.usingMocks"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Showing UI preview data until the CMS API is connected.
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

    <AppAlert :errors="cms.errors" />

    <VCard>
      <VCardItem>
        <VCardTitle>Owner Agreement Policy</VCardTitle>
      </VCardItem>

      <VProgressLinear
        v-if="cms.loading && !cms.data"
        indeterminate
      />

      <VCardText>
        <VForm @submit.prevent="submit">
          <RichTextEditor
            v-model="form.content"
            label="Content"
            :error="cms.errors.content"
            :disabled="cms.loading && !cms.data"
            placeholder="Write the owner agreement policy…"
            hint="Rich text for the owner agreement shown to owners"
          />

          <div class="d-flex flex-wrap justify-end gap-2 mt-4">
            <BaseButton
              type="button"
              variant="tonal"
              label="Preview"
              prepend-icon="bx-show"
              :disabled="!form.content"
              @click="openPreview"
            />
            <BaseButton
              type="submit"
              color="success"
              label="Save"
              prepend-icon="bx-save"
              :loading="cms.loading"
            />
          </div>
        </VForm>

        <p
          v-if="cms.data?.updated_at"
          class="text-caption text-medium-emphasis mt-4 mb-0"
        >
          Last updated: {{ cms.data.updated_at }}
        </p>
      </VCardText>
    </VCard>

    <VDialog
      v-model="previewOpen"
      max-width="960"
      scrollable
    >
      <VCard>
        <VCardItem>
          <VCardTitle class="text-center w-100">
            New site updates and agreement to terms of use
          </VCardTitle>
        </VCardItem>
        <VCardText>
          <!-- eslint-disable-next-line vue/no-v-html -- trusted CMS HTML / UI preview -->
          <div
            class="cms-preview text-body-1"
            v-html="form.content"
          />
        </VCardText>
        <VCardActions class="justify-center flex-wrap gap-2 pb-4">
          <BaseButton
            color="success"
            label="I agree"
            disabled
          />
          <BaseButton
            variant="tonal"
            label="Decline"
            disabled
          />
          <VSpacer />
          <BaseButton
            variant="tonal"
            label="Close"
            @click="previewOpen = false"
          />
        </VCardActions>
        <VCardText class="pt-0 text-center text-caption text-medium-emphasis">
          <p class="mb-1">
            <a
              href="https://www.airconcierge.net/terms-of-use"
              target="_blank"
              rel="noopener noreferrer"
            >Air Concierge Management Terms</a>
          </p>
          <p class="mb-1">
            <a
              href="https://www.airconcierge.net/privacy-policy"
              target="_blank"
              rel="noopener noreferrer"
            >Air Concierge Privacy Statement</a>
          </p>
          <p class="mb-0">
            <a
              href="https://www.airconcierge.net/faq-full-time-management"
              target="_blank"
              rel="noopener noreferrer"
            >Frequently Asked Questions</a>
          </p>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.cms-preview :deep(h1),
.cms-preview :deep(h2),
.cms-preview :deep(h3),
.cms-preview :deep(h4),
.cms-preview :deep(h5) {
  margin-block: 0.75em 0.35em;
  line-height: 1.25;
}

.cms-preview :deep(p),
.cms-preview :deep(ul),
.cms-preview :deep(ol),
.cms-preview :deep(blockquote) {
  margin-block: 0.5em;
}

.cms-preview :deep(ul),
.cms-preview :deep(ol) {
  padding-inline-start: 1.25rem;
}

.cms-preview :deep(blockquote) {
  border-inline-start: 3px solid rgba(var(--v-theme-on-surface), 0.2);
  padding-inline-start: 0.85rem;
}

.cms-preview :deep(a) {
  color: rgb(var(--v-theme-primary));
}

.cms-preview :deep(img) {
  max-width: 100%;
  height: auto;
}

.cms-preview :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin-block: 1rem;
}

.cms-preview :deep(td),
.cms-preview :deep(th) {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding: 0.45rem 0.6rem;
}
</style>
