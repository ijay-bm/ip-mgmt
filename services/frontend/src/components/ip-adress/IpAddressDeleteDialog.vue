<template>
  <v-dialog v-model="isOpen" max-width="400" persistent>
    <v-card>
      <v-card-title class="text-title-large pt-6 px-6">
        Delete IP Address
      </v-card-title>

      <v-card-text class="px-6">
        <p class="text-body-medium text-medium-emphasis">
          Are you sure you want to delete this IP address? This action cannot be
          undone.
        </p>

        <div class="text-label-medium text-medium-emphasis mb-1">
          IP Address
        </div>
        <div class="text-body-large">{{ ipAddress?.ip_address }}</div>

        <div class="text-label-medium text-medium-emphasis mt-3 mb-1">
          Label
        </div>
        <div class="text-body-large">{{ ipAddress?.label }}</div>

        <template v-if="ipAddress?.comment">
          <div class="text-label-medium text-medium-emphasis mt-3 mb-1">
            Comment
          </div>
          <div class="text-body-medium text-medium-emphasis">
            {{ ipAddress?.comment }}
          </div>
        </template>
      </v-card-text>

      <v-card-actions class="px-6 pb-6">
        <v-spacer />
        <v-btn :disabled="isLoading" variant="text" @click="close">
          Cancel
        </v-btn>
        <v-btn
          color="error"
          :loading="isLoading"
          variant="tonal"
          @click="handleDelete"
        >
          Delete
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import type { IpAddress } from "../../types/IpAddress";
import { ref } from "vue";
import { ipAddressResource } from "../../resources/ip-address";
import { useToasterStore } from "../../stores/toaster";

const isOpen = defineModel<boolean>();

const props = defineProps<{
  ipAddress: IpAddress | null;
}>();

const emit = defineEmits<{
  deleted: [];
}>();

const isLoading = ref(false);

async function handleDelete() {
  if (!props.ipAddress) return;

  isLoading.value = true;
  try {
    await ipAddressResource.destroy(props.ipAddress.id);
    emit("deleted");

    useToasterStore().add({
      text: "IP address deleted",
      color: "success",
    });
    close();
  } finally {
    isLoading.value = false;
  }
}

function close() {
  isOpen.value = false;
}
</script>
