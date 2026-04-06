<template>
  <v-text-field
    v-model="model"
    clearable
    hide-details
    :label="label"
    prepend-inner-icon="mdi-calendar-clock"
    type="datetime-local"
  />
</template>

<script setup lang="ts">
defineProps<{
  label: string;
}>();

const model = defineModel<string | null>({
  default: null,
});
</script>
