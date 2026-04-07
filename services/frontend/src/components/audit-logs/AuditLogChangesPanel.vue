<template>
  <div>
    <div
      v-if="!hasChanges && !hasProperties"
      class="text-caption text-medium-emphasis"
    >
      No detailed change data available for this entry.
    </div>

    <div class="d-flex flex-wrap ga-4">
      <div v-if="hasChanges" style="min-width: 280px; flex: 1">
        <div
          class="text-caption text-medium-emphasis font-weight-medium mb-2 text-uppercase letter-spacing-1"
        >
          Changes
        </div>

        <v-table density="compact">
          <thead>
            <tr>
              <th class="text-caption">Field</th>
              <th class="text-caption">Before</th>
              <th class="text-caption">After</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="field in changedFields" :key="field">
              <td class="text-caption font-weight-medium">
                {{ field }}
              </td>
              <td class="text-caption text-error">
                <span v-if="oldValues[field] !== undefined">
                  {{ formatValue(oldValues[field]) }}
                </span>
                <span v-else class="text-medium-emphasis">—</span>
              </td>
              <td class="text-caption text-success">
                <span v-if="newValues[field] !== undefined">
                  {{ formatValue(newValues[field]) }}
                </span>
                <span v-else class="text-medium-emphasis">—</span>
              </td>
            </tr>
          </tbody>
        </v-table>
      </div>

      <div v-if="hasProperties" style="min-width: 240px; flex: 1">
        <div
          class="text-caption text-medium-emphasis font-weight-medium mb-2 text-uppercase letter-spacing-1"
        >
          Request context
        </div>

        <div class="d-flex flex-column ga-1">
          <div
            v-for="(val, key) in displayProperties"
            :key="key"
            class="d-flex align-start ga-2"
          >
            <span
              class="text-caption text-medium-emphasis text-no-wrap"
              style="min-width: 96px"
            >
              {{ sessionKeyLabels[key] || key }}
            </span>
            <code class="text-caption" style="word-break: break-all">
              {{ val }}
            </code>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { AuditLog } from "../../types/AuditLog";
import { computed } from "vue";

const props = defineProps<{ log: AuditLog }>();

const sessionKeyLabels: Record<string, string> = {
  id: "Session ID",
  ip: "IP Address",
  user_agent: "User Agent",
};

const sessionKeys: Array<keyof AuditLog["session"]> = [
  "id",
  "ip",
  "user_agent",
];

const oldValues = computed<Record<string, any>>(
  () => props.log.changes?.old ?? {},
);
const newValues = computed<Record<string, any>>(
  () => props.log.changes?.attributes ?? {},
);

const changedFields = computed(() => {
  const allKeys = new Set([
    ...Object.keys(oldValues.value),
    ...Object.keys(newValues.value),
  ]);
  return [...allKeys];
});

const hasChanges = computed(() => changedFields.value.length > 0);

const displayProperties = computed(() => {
  const session = props.log.session;
  if (!session) return {};
  return Object.fromEntries(
    sessionKeys
      .filter((sessionKey) => session[sessionKey])
      .map((sessionKey) => [sessionKey, session[sessionKey]]),
  );
});

const hasProperties = computed(
  () => Object.keys(displayProperties.value).length > 0,
);

function formatValue(val: unknown): string {
  if (val === null || val === undefined) return "—";
  if (typeof val === "object") return JSON.stringify(val);
  return String(val);
}
</script>

<style scoped>
.letter-spacing-1 {
  letter-spacing: 0.05em;
}
</style>
