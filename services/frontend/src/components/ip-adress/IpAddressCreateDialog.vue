<template>
  <v-dialog v-model="isOpen" max-width="500" persistent>
    <v-card>
      <v-card-title class="text-title-large pt-6 px-6">
        Add IP Address
      </v-card-title>

      <v-form @submit.prevent="handleSubmit">
        <v-card-text>
          <v-text-field
            v-model="form.ip_address"
            class="mb-3"
            density="compact"
            :error-messages="errors.ip_address"
            label="IP Address"
            placeholder="e.g. 192.168.1.1 or 2001:db8::1"
            variant="outlined"
          />
          <v-text-field
            v-model="form.label"
            class="mb-3"
            density="compact"
            :error-messages="errors.label"
            label="Label"
            variant="outlined"
          />
          <v-textarea
            v-model="form.comment"
            auto-grow
            density="compact"
            :error-messages="errors.comment"
            label="Comment (optional)"
            rows="3"
            variant="outlined"
          />
        </v-card-text>

        <v-card-actions class="px-6 pb-6">
          <v-spacer />
          <v-btn variant="text" @click="close">Cancel</v-btn>
          <v-btn
            color="green-accent-3"
            :loading="isLoading"
            type="submit"
            variant="tonal"
          >
            Create
          </v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useApiErrorHandler } from "../../composables/useApiErrorHandler";
import { ipAddressResource } from "../../resources/ip-address";
import { useToasterStore } from "../../stores/toaster";

const isOpen = defineModel<boolean>();

const emit = defineEmits<{
  created: [];
}>();

const isLoading = ref(false);

const form = ref({
  ip_address: "",
  label: "",
  comment: "",
});

const errors = ref<Record<string, string[]>>({});

const { handle } = useApiErrorHandler();

async function handleSubmit() {
  errors.value = {};
  isLoading.value = true;
  try {
    await ipAddressResource.store(form.value);
    emit("created");
    useToasterStore().add({
      text: "IP address created",
      color: "success",
    });
    close();
  } catch (error) {
    handle(error, errors);
  } finally {
    isLoading.value = false;
  }
}

function close() {
  isOpen.value = false;
}

function onOpen() {
  form.value.ip_address = "";
  form.value.label = "";
  form.value.comment = "";
  errors.value = {};
}

watch(isOpen, (value) => {
  if (value) onOpen();
});
</script>
