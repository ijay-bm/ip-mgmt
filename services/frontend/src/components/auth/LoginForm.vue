<template>
  <div class="login-form">
    <div>
      <v-img class="mx-auto my-6" max-width="80" src="/logo.svg"></v-img>
    </div>
    <v-card
      class="mx-auto px-12 pt-6 pb-8"
      elevation="3"
      max-width="448"
      rounded="lg"
    >
      <v-card-title class="text-h4 mb-2 text-center">Hello</v-card-title>

      <v-form @submit.prevent="onSubmit">
        <label class="text-body-large text-medium-emphasis" for="email">
          Email
        </label>

        <v-text-field
          id="email"
          v-model="form.email"
          density="compact"
          :disabled="isLoading"
          :error-messages="errors.email"
          placeholder="Email address"
          prepend-inner-icon="mdi-at"
          variant="outlined"
        ></v-text-field>

        <label class="text-body-large text-medium-emphasis" for="password">
          Password
        </label>

        <v-text-field
          id="password"
          v-model="form.password"
          :append-inner-icon="isPasswordVisible ? 'mdi-eye-off' : 'mdi-eye'"
          density="compact"
          :disabled="isLoading"
          :error-messages="errors.password"
          placeholder="Enter your password"
          prepend-inner-icon="mdi-lock-outline"
          :type="isPasswordVisible ? 'text' : 'password'"
          variant="outlined"
          @click:append-inner="isPasswordVisible = !isPasswordVisible"
        ></v-text-field>

        <v-btn
          block
          class="mt-3"
          color="green-accent-3"
          :loading="isLoading"
          size="large"
          type="submit"
          variant="tonal"
        >
          Log In
        </v-btn>
      </v-form>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useApiErrorHandler } from "../../composables/useApiErrorHandler";
import { useAuthStore } from "../../stores/auth";

const { login } = useAuthStore();

const isPasswordVisible = ref(false);

const { handle } = useApiErrorHandler();

const form = ref({
  email: "",
  password: "",
});

const errors = ref<Record<string, string[]>>({});

const isLoading = ref(false);
async function onSubmit() {
  try {
    if (isLoading.value) {
      return;
    }

    isLoading.value = true;
    errors.value = {};

    await login(form.value);
  } catch (error) {
    handle(error, errors);
  } finally {
    isLoading.value = false;
  }
}
</script>

<style scoped>
.login-form {
  max-width: 450px;
  width: 100%;
  min-width: 320px;
  transform: translateY(-100px);
}
</style>
