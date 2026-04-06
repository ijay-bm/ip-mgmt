<template>
  <v-dialog v-model="isOpen" max-width="500" persistent>
    <v-card>
      <v-card-title class="text-title-large pt-6 px-6">
        Edit IP Address
      </v-card-title>

      <v-form @submit.prevent="handleSubmit">
        <v-card-text class="px-6">
          <v-text-field
            class="mb-3"
            density="compact"
            disabled
            label="IP Address"
            :model-value="ipAddress?.ip_address"
            readonly
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
            Save
          </v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import type { IpAddress } from "../../types/IpAddress";
import { ref, watch } from "vue";
import { useApiErrorHandler } from "../../composables/useApiErrorHandler";
import { ipAddressResource } from "../../resources/ip-address";
import { useToasterStore } from "../../stores/toaster";

const isOpen = defineModel<boolean>();

const props = defineProps<{
  ipAddress: IpAddress | null;
}>();

const emit = defineEmits<{
  updated: [];
}>();

const isLoading = ref(false);

const form = ref({
  label: "",
  comment: "",
});

const errors = ref<Record<string, string[]>>({});

const { handle } = useApiErrorHandler();

async function handleSubmit() {
  if (!props.ipAddress) return;

  errors.value = {};
  isLoading.value = true;
  try {
    await ipAddressResource.update(props.ipAddress.id, form.value);
    emit("updated");
    useToasterStore().add({
      text: "IP address updated",
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
  form.value.label = props.ipAddress?.label || "";
  form.value.comment = props.ipAddress?.comment || "";
  errors.value = {};
}

watch(isOpen, (value) => {
  if (value) onOpen();
});
</script>
