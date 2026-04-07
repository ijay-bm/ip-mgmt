<template>
  <v-app-bar
    v-if="isAuthenticated"
    class="px-3"
    collapse-position="end"
    :collpase="true"
    elevation="1"
    floating
    height="72"
  >
    <template #prepend>
      <v-app-bar-nav-icon>
        <router-link to="/ip-management">
          <v-img alt="Logo" height="40" src="/logo.svg" width="40" />
        </router-link>
      </v-app-bar-nav-icon>
    </template>

    <v-btn
      class="text-body-large"
      :color="
        route.path.startsWith('/ip-management') ? 'green-accent-3' : 'white'
      "
      to="/ip-management"
      variant="text"
    >
      IP Mangement
    </v-btn>

    <v-menu v-if="isSuperAdmin">
      <template #activator="{ props }">
        <v-btn
          v-bind="props"
          append-icon="mdi-menu-down"
          :color="
            route.path.startsWith('/audit-logs')
              ? 'green-accent-3'
              : props.color
          "
        >
          Audit Logs
        </v-btn>
      </template>

      <v-list slim>
        <v-list-item color="text-green-accent-3" :to="'/audit-logs/users'">
          <template #prepend>
            <v-icon>mdi-account-group</v-icon>
          </template>
          Users
        </v-list-item>

        <v-divider></v-divider>

        <v-list-item
          color="text-green-accent-3"
          :to="'/audit-logs/ip-addresses'"
        >
          <template #prepend>
            <v-icon>mdi-numeric</v-icon>
          </template>
          IP Addresses
        </v-list-item>
      </v-list>
    </v-menu>

    <v-spacer />

    <v-menu v-if="isAuthenticated">
      <template #activator="{ props }">
        <v-btn v-bind="props" append-icon="mdi-menu-down" color="plain">
          {{ user?.name }}
        </v-btn>
      </template>

      <v-list slim>
        <v-list-item>
          <template #prepend>
            <v-icon>mdi-account</v-icon>
          </template>
          <v-list-item-title class="text-disabled">
            {{ user?.email }}
          </v-list-item-title>
        </v-list-item>

        <v-divider></v-divider>

        <v-list-item
          @click="
            () => {
              logout();
            }
          "
        >
          <template #prepend>
            <v-icon>mdi-exit-run</v-icon>
          </template>
          <v-list-item-title>Leave this place</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-app-bar>
</template>

<script setup lang="ts">
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";

const route = useRoute();

const authStore = useAuthStore();

const { logout } = authStore;

const { user, isAuthenticated, isSuperAdmin } = storeToRefs(authStore);
</script>

<style scoped>
:deep(.v-btn--active:not(:hover) .v-btn__overlay) {
  opacity: 0 !important;
}

:deep(.v-btn--active .v-btn__overlay) {
  opacity: calc(var(--v-hover-opacity) * var(--v-theme-overlay-multiplier));
}
</style>
