<template>
  <v-menu
    v-model="menu"
    :close-on-content-click="false"
    location="bottom start"
    transition="scale-transition"
  >
    <template #activator="{ props: activatorProps }">
      <v-btn v-bind="activatorProps" prepend-icon="mdi-calendar-range">
        Date Range
        <v-icon
          v-if="hasActiveFilter"
          color="green-accent-3"
          end
          size="x-small"
        >
          mdi-circle
        </v-icon>
      </v-btn>
    </template>

    <v-card class="d-flex flex-column ga-4 pa-4 flex-column" width="300">
      <DateTimeInput v-model="from" class="w-full" label="From" />

      <DateTimeInput v-model="to" class="w-full" label="To" />
    </v-card>
  </v-menu>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import DateTimeInput from "./DateTimeInput.vue";

const menu = ref(false);

const from = defineModel<string | null>("from", { default: null });
const to = defineModel<string | null>("to", { default: null });

const hasActiveFilter = computed(() => !!from.value || !!to.value);
</script>
