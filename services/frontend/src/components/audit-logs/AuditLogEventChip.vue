<template>
  <v-chip
    :color="config.color"
    density="compact"
    label
    :prepend-icon="config.icon"
    variant="tonal"
  >
    {{ event }}
  </v-chip>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{ event: string }>();

const eventMap: Record<string, { color: string; icon: string }> = {
  created: { color: "success", icon: "mdi-plus-circle-outline" },
  updated: { color: "info", icon: "mdi-pencil-outline" },
  deleted: { color: "error", icon: "mdi-delete-outline" },
  login: { color: "primary", icon: "mdi-login" },
  logout: { color: "warning", icon: "mdi-logout" },
};

const config = computed<{ color: string; icon: string }>(
  () => eventMap[props.event?.toLowerCase()] ?? {},
);
</script>
