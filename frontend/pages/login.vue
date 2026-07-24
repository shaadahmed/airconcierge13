<script setup>
const auth = useAuthStore()

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const isPasswordVisible = ref(false)

const submit = async () => {
  try {
    await auth.login(form.value)
    await navigateTo('/admin/dashboard')
  }
  catch {
    // Validation errors are exposed by the store.
  }
}

definePageMeta({ layout: 'blank', middleware: 'guest' })
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <VCardItem class="justify-center">
          <BaseLink
            to="/"
            class="app-logo"
          >
            <h1 class="app-logo-title">Air Concierge</h1>
          </BaseLink>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Welcome back
          </h4>
          <p class="mb-0">Sign in to manage Air Concierge.</p>
        </VCardText>

        <VCardText>
          <VAlert
            v-if="auth.errors.general"
            type="error"
            class="mb-4"
          >
            {{ auth.errors.general[0] }}
          </VAlert>
          <VForm @submit.prevent="submit">
            <VRow>
              <VCol cols="12">
                <BaseInput
                  v-model="form.email"
                  autofocus
                  label="Email"
                  type="email"
                  autocomplete="email"
                  :error="auth.errors.email"
                  required
                />
              </VCol>

              <VCol cols="12">
                <BaseInput
                  v-model="form.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  :error="auth.errors.password"
                  required
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="my-4">
                  <BaseCheckbox
                    v-model="form.remember"
                    label="Remember me"
                  />
                </div>

                <BaseButton
                  block
                  type="submit"
                  label="Login"
                  :loading="auth.loading"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core/scss/template/pages/page-auth";
</style>
