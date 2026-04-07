<template>
  <div class="d-flex align-center ga-4 mb-4">
    <v-text-field
      v-model="search"
      clearable
      density="compact"
      hide-details
      placeholder="Search audit logs..."
      prepend-inner-icon="mdi-magnify"
      @update:model-value="handleSearch"
    />

    <DateRangeFilter
      :from="dateFrom"
      :to="dateTo"
      @update:from="dateFrom = $event"
      @update:to="dateTo = $event"
    />

    <v-menu
      :close-on-content-click="false"
      location="bottom start"
      transition="scale-transition"
    >
      <template #activator="{ props: activatorProps }">
        <v-btn v-bind="activatorProps" prepend-icon="mdi-filter">
          Other Filters
        </v-btn>
      </template>

      <v-card class="d-flex flex-column ga-4 pa-4 flex-column" width="300">
        <v-text-field
          v-model="filters.event"
          clearable
          hide-details
          placeholder="Filter by Event"
          prepend-inner-icon="mdi-shape"
          @update:model-value="handleFilterChange"
        />

        <v-text-field
          v-model="filters.causer_name"
          clearable
          hide-details
          placeholder="Filter by User"
          prepend-inner-icon="mdi-account-details"
          @update:model-value="handleFilterChange"
        />

        <v-text-field
          v-model="filters.causer_email"
          clearable
          hide-details
          placeholder="Filter by Email"
          prepend-inner-icon="mdi-at"
          @update:model-value="handleFilterChange"
        />

        <v-text-field
          v-model="filters.id"
          clearable
          hide-details
          placeholder="Filter by Session ID"
          prepend-inner-icon="mdi-identifier"
          @update:model-value="handleFilterChange"
        />

        <v-text-field
          v-model="filters.ip"
          clearable
          hide-details
          placeholder="Filter by IP"
          prepend-inner-icon="mdi-ip-network"
          @update:model-value="handleFilterChange"
        />
        <v-btn
          :disabled="!hasActiveFilters"
          prepend-icon="mdi-filter-remove-outline"
          size="small"
          variant="text"
          @click="clearAllFilters"
        >
          Clear filters
        </v-btn>
      </v-card>
    </v-menu>

    <v-menu :close-on-content-click="false" location="bottom end">
      <template #activator="{ props }">
        <v-btn v-bind="props" prepend-icon="mdi-eye" variant="tonal">
          Columns
        </v-btn>
      </template>
      <v-list density="compact">
        <v-list-item v-for="col in toggleableColumns" :key="col.key">
          <template #prepend>
            <v-checkbox-btn v-model="visibleColumns" :value="col.key" />
          </template>
          <v-list-item-title>{{ col.title }}</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </div>

  <v-data-table-server
    :headers="visibleHeaders"
    item-key="id"
    :items="records"
    :items-length="pagination?.meta.total || 0"
    :items-per-page="itemsPerPage"
    :items-per-page-options="[
      { value: 10, title: '10' },
      { value: 25, title: '25' },
      { value: 50, title: '50' },
      { value: 100, title: '100' },
    ]"
    :loading="isLoading"
    multi-sort
    must-sort
    :page="currentPage"
    show-expand
    sort-asc-icon="mdi-menu-up"
    :sort-by="sortBy"
    sort-desc-icon="mdi-menu-down"
    @update:options="handleOptions"
  >
    <template #item.event="{ value }">
      <AuditLogEventChip :event="value" />
    </template>

    <template #item.causer="{ value }">
      <div v-if="value" class="d-flex flex-column">
        <div class="text-body-2 font-weight-medium text-no-wrap">
          {{ value.name }}
        </div>
        <div class="text-caption text-medium-emphasis text-no-wrap">
          {{ value.email }}
        </div>
      </div>
      <span v-else class="text-medium-emphasis text-caption">—</span>
    </template>

    <template #item.session.id="{ item }">
      <v-chip
        v-if="item.session?.id"
        class="font-weight-mono"
        label
        size="small"
      >
        {{ item.session.id }}
      </v-chip>
      <span v-else class="text-medium-emphasis text-caption">—</span>
    </template>

    <template #item.session.ip="{ item }">
      <v-chip
        v-if="item.session?.ip"
        class="font-weight-mono"
        label
        size="small"
      >
        {{ item.session.ip }}
      </v-chip>
      <span v-else class="text-medium-emphasis text-caption">—</span>
    </template>

    <template #item.description="{ value }">
      <span class="text-body-2">{{ value }}</span>
    </template>

    <template #item.created_at="{ value }">
      <span class="text-no-wrap text-body-2">{{ formatDate(value) }}</span>
    </template>

    <template #expanded-row="{ item }">
      <tr>
        <td class="pa-0" :colspan="visibleHeaders.length + 1">
          <div class="py-4 px-8">
            <AuditLogChangesPanel :log="item" />
          </div>
        </td>
      </tr>
    </template>
  </v-data-table-server>
</template>

<script setup lang="ts">
import type { AuditLog } from "../../types/AuditLog";
import type { Pagination } from "../../types/Pagination";
import type { DataTableSortItem } from "vuetify";
import { useDebounceFn } from "@vueuse/core";
import { computed, reactive, ref, watch } from "vue";
import AuditLogChangesPanel from "../../components/audit-logs/AuditLogChangesPanel.vue";
import AuditLogEventChip from "../../components/audit-logs/AuditLogEventChip.vue";
import DateRangeFilter from "../../components/common/DateRangeFilter.vue";
import { userAuditLogResource } from "../../resources/user-audit-logs";
import { formatDate } from "../../utils/date";

const headers = [
  { key: "id", title: "ID", nowrap: true, sortable: true },
  { key: "event", title: "Event", nowrap: true, sortable: true },
  { key: "description", title: "Description", nowrap: false, sortable: false },
  { key: "causer", title: "User", nowrap: true, sortable: false },
  {
    key: "session.id",
    title: "Session",
    nowrap: true,
    sortable: false,
  },
  { key: "session.ip", title: "Client IP", nowrap: true, sortable: false },
  { key: "created_at", title: "Timestamp", nowrap: true, sortable: true },
];

const toggleableColumns = headers;

const visibleColumns = ref([
  "event",
  "causer",
  "session.id",
  "session.ip",
  "created_at",
]);

const visibleHeaders = computed(() =>
  headers.filter((h) => visibleColumns.value.includes(h.key)),
);

const isLoading = ref(false);
const records = ref<AuditLog[]>([]);
const pagination = ref<Pagination | null>(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const search = ref("");
const sortBy = ref<DataTableSortItem[]>([{ key: "created_at", order: "desc" }]);
const dateFrom = ref<string | null>(null);
const dateTo = ref<string | null>(null);

const filters = reactive<Record<string, string | null>>({
  event: null,
  causer_name: null,
  causer_email: null,
  id: null,
  ip: null,
});

const hasActiveFilters = computed(
  () =>
    Object.values(filters).some(Boolean) || !!dateFrom.value || !!dateTo.value,
);

async function callIndex(
  page = 1,
  perPage = 10,
  sortItems: DataTableSortItem[] = [],
) {
  isLoading.value = true;
  try {
    const params: Record<string, any> = { page, per_page: perPage };

    if (search.value) params["filter[search]"] = search.value;

    if (dateFrom.value)
      params["filter[created_at_from]"] = new Date(
        dateFrom.value,
      ).toISOString();
    if (dateTo.value)
      params["filter[created_at_to]"] = new Date(dateTo.value).toISOString();

    for (const [key, val] of Object.entries(filters)) {
      if (val) params[`filter[${key}]`] = val;
    }

    if (sortItems.length > 0) {
      params.sort = sortItems
        .map((s) => (s.order === "desc" ? `-${s.key}` : s.key))
        .join(",");
    }

    const response = await userAuditLogResource.index(params);
    const { data, ...responsePagination } = response.data;

    records.value = data || [];
    pagination.value = responsePagination || null;
  } finally {
    isLoading.value = false;
  }
}

const handleSearch = useDebounceFn(() => {
  currentPage.value = 1;
  callIndex(1, itemsPerPage.value, sortBy.value);
}, 300);

const handleFilterChange = useDebounceFn(() => {
  currentPage.value = 1;
  callIndex(1, itemsPerPage.value, sortBy.value);
}, 300);

const handleOptions = useDebounceFn(
  ({
    page,
    itemsPerPage: perPage,
    sortBy: newSortBy,
  }: {
    page: number;
    itemsPerPage: number;
    sortBy: DataTableSortItem[];
  }) => {
    currentPage.value = page;
    itemsPerPage.value = perPage;
    sortBy.value = newSortBy;
    callIndex(page, perPage, newSortBy);
  },
  300,
);

const handleDateChange = useDebounceFn(() => {
  currentPage.value = 1;
  callIndex(1, itemsPerPage.value, sortBy.value);
}, 300);

watch([dateFrom, dateTo], handleDateChange);

function clearAllFilters() {
  for (const key of Object.keys(filters)) filters[key] = null;
  dateFrom.value = null;
  dateTo.value = null;
  search.value = "";
  currentPage.value = 1;
  callIndex(1, itemsPerPage.value, sortBy.value);
}
</script>

<style scoped>
.font-weight-mono {
  font-family: monospace;
}
</style>
