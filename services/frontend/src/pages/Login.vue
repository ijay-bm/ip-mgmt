<template>
  <v-layout class="login-page">
    <LoginForm />
  </v-layout>
</template>

<script setup lang="ts">
import LoginForm from "../components/auth/LoginForm.vue";
</script>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}
</style>
