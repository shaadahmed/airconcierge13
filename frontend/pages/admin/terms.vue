<script setup>
/* eslint-disable vue/no-v-html -- agreement HTML comes from trusted Laravel CMS content */
const terms = useTermsStore()
const auth = useAuthStore()
const router = useRouter()

const confirmDisagree = ref(false)

onMounted(() => terms.load())

const onAgree = async () => {
  try {
    const result = await terms.agree()

    await router.push(result.redirect_to || '/admin/dashboard')
  }
  catch {
    // Store exposes errors.
  }
}

const onDisagree = async () => {
  try {
    const result = await terms.disagree()

    confirmDisagree.value = false
    auth.user = null
    await router.push(result.redirect_to || '/login')
  }
  catch {
    // Store exposes errors.
  }
}

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Owner terms"
      subtitle="Review and accept the owner agreement to continue"
    />

    <VCard title="Agreement">
      <VCardText>
        <AppAlert :errors="terms.errors" />

        <VProgressLinear
          v-if="terms.loading && !terms.data"
          indeterminate
          class="mb-4"
        />

        <VAlert
          v-if="terms.data?.agreed"
          type="success"
          class="mb-4"
        >
          You have already agreed to these terms.
        </VAlert>

        <!-- eslint-disable-next-line vue/no-v-html -- trusted CMS HTML from Laravel dynamic_content -->
        <div
          v-if="terms.data?.content"
          class="text-body-1 mb-6"
          v-html="terms.data.content"
        />
        <EmptyState
          v-else-if="!terms.loading"
          title="Terms content unavailable"
          description="Contact support if this persists."
        />

        <p
          v-if="terms.data?.content_updated_at"
          class="text-caption text-medium-emphasis mb-4"
        >
          Last updated: {{ terms.data.content_updated_at }}
        </p>

        <div
          v-if="terms.data && !terms.data.agreed"
          class="d-flex flex-wrap gap-2"
        >
          <BaseButton
            label="Accept terms"
            color="primary"
            :loading="terms.loading"
            @click="onAgree"
          />
          <BaseButton
            label="Decline"
            variant="tonal"
            color="error"
            :loading="terms.loading"
            @click="confirmDisagree = true"
          />
        </div>
      </VCardText>
    </VCard>

    <ConfirmDialog
      v-model="confirmDisagree"
      title="Decline owner terms?"
      message="Declining will end your session (legacy behavior)."
      confirm-label="Decline and log out"
      confirm-color="error"
      :loading="terms.loading"
      @confirm="onDisagree"
    />
  </div>
</template>
