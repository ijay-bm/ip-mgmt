<template>
  <v-app>
    <v-layout class="border">
      <Navbar />
      <v-main>
        <v-container max-width="1280">
          <v-breadcrumbs
            v-if="!route.path.includes('/login')"
            class="pa-0"
            :items="route?.path?.split('/')"
          ></v-breadcrumbs>
          <router-view />
        </v-container>
      </v-main>
    </v-layout>

    <v-snackbar-queue
      ref="snackbarQueue"
      v-model="queue"
      closable
      location="bottom right"
      :total-visible="5"
    ></v-snackbar-queue>
  </v-app>
</template>

<script lang="ts" setup>
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import Navbar from "./components/Navbar.vue";
import { useToasterStore } from "./stores/toaster";

const route = useRoute();

const toasterStore = useToasterStore();

const { queue } = storeToRefs(toasterStore);
</script>
