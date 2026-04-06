<template>
  <div class="d-flex align-center ga-4 mb-4">
    <v-text-field
      v-model="search"
      clearable
      density="compact"
      hide-details
      placeholder="Search by IP, label or comment..."
      prepend-inner-icon="mdi-magnify"
      @update:model-value="handleSearch"
    />

    <DateRangeFilter
      :from="dateFrom"
      :to="dateTo"
      @update:from="dateFrom = $event"
      @update:to="dateTo = $event"
    />

    <v-menu :close-on-content-click="false" location="bottom end">
      <template #activator="{ props }">
        <v-btn v-bind="props" prepend-icon="mdi-eye">Columns</v-btn>
      </template>
      <v-list>
        <v-list-item
          v-for="toggleableColumn in toggleableColumns"
          :key="toggleableColumn.key"
        >
          <template #prepend>
            <v-checkbox-btn
              v-model="visibleColumns"
              :value="toggleableColumn.key"
            />
          </template>
          <v-list-item-title>{{ toggleableColumn.title }}</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>

    <v-btn
      color="green-accent-3"
      prepend-icon="mdi-plus"
      variant="tonal"
      @click="showCreateDialog = true"
    >
      Add IP Address
    </v-btn>
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
    sort-asc-icon="mdi-menu-up"
    :sort-by="sortBy"
    sort-desc-icon="mdi-menu-down"
    @update:options="handleOptions"
  >
    <template #item.label="{ value }">
      <div
        class="text-body-large text-high-emphasis"
        :style="{ whiteSpace: 'break-spaces', width: '300px' }"
      >
        {{ value }}
      </div>
    </template>

    <template #item.comment="{ value }">
      <div
        class="text-body-medium text-medium-emphasis"
        :style="{ whiteSpace: 'break-spaces', width: '300px' }"
      >
        {{ value }}
      </div>
    </template>

    <template #item.created_at="{ value }">
      <span class="text-no-wrap">{{ formatDate(value) }}</span>
    </template>

    <template #item.updated_at="{ value }">
      <span class="text-no-wrap">{{ formatDate(value) }}</span>
    </template>

    <template #item.actions="{ item }">
      <div class="d-flex ga-1">
        <v-btn
          density="compact"
          icon
          variant="text"
          @click.stop="openEditDialog(item)"
        >
          <v-icon>mdi-pencil</v-icon>
        </v-btn>

        <v-btn
          v-if="isSuperAdmin"
          color="error"
          density="compact"
          icon
          variant="text"
          @click.stop="openDeleteDialog(item)"
        >
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </div>
    </template>
  </v-data-table-server>

  <IpAddressCreateDialog v-model="showCreateDialog" @created="callIndex()" />

  <IpAddressEditDialog
    v-model="showEditDialog"
    :ip-address="selectedIpAddress"
    @updated="callIndex()"
  />

  <IpAddressDeleteDialog
    v-model="showDeleteDialog"
    :ip-address="selectedIpAddress"
    @deleted="callIndex()"
  />
</template>

<script setup lang="ts">
import type { IpAddress } from "../types/IpAddress";
import type { Pagination } from "../types/Pagination";
import type { DataTableSortItem } from "vuetify";
import { useDebounceFn } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";
import DateRangeFilter from "../components/common/DateRangeFilter.vue";
import IpAddressCreateDialog from "../components/ip-adress/IpAddressCreateDialog.vue";
import IpAddressDeleteDialog from "../components/ip-adress/IpAddressDeleteDialog.vue";
import IpAddressEditDialog from "../components/ip-adress/IpAddressEditDialog.vue";
import { ipAddressResource } from "../resources/ip-address";
import { useAuthStore } from "../stores/auth";
import { formatDate } from "../utils/date";

const { isSuperAdmin } = storeToRefs(useAuthStore());

const headers = [
  { title: "ID", key: "id", nowrap: true },
  { title: "User ID", key: "user_id", nowrap: true },
  {
    title: "IP Address",
    key: "ip_address",
    nowrap: true,
  },
  { title: "Label", key: "label", nowrap: true },
  { title: "Comment", key: "comment", nowrap: true },
  {
    title: "Created At",
    key: "created_at",
    nowrap: true,
  },
  {
    title: "Updated At",
    key: "updated_at",
    nowrap: true,
  },
  { title: "", key: "actions", nowrap: true, sortable: false },
];

const isLoading = ref(false);
const records = ref<IpAddress[]>([]);
const pagination = ref<Pagination>(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const search = ref("");
const sortBy = ref<DataTableSortItem[]>([{ key: "created_at", order: "desc" }]);
const dateFrom = ref<string | null>(null);
const dateTo = ref<string | null>(null);

const toggleableColumns = headers.filter((header) => header.key !== "actions");
const visibleColumns = ref([
  "ip_address",
  "label",
  "comment",
  "created_at",
  "actions",
]);
const visibleHeaders = computed(() =>
  headers.filter((h) => visibleColumns.value.includes(h.key)),
);

const selectedIpAddress = ref<IpAddress | null>(null);
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);

async function callIndex(
  page = 1,
  perPage = 10,
  sortBy: DataTableSortItem[] = [],
) {
  isLoading.value = true;
  try {
    const params: Record<string, any> = {
      page,
      per_page: perPage,
    };

    if (search.value) params["filter[search]"] = search.value;

    if (dateFrom.value)
      params["filter[created_at_from]"] = new Date(
        dateFrom.value,
      ).toISOString();
    if (dateTo.value)
      params["filter[created_at_to]"] = new Date(dateTo.value).toISOString();

    if (sortBy.length > 0) {
      params.sort = sortBy
        .map((s) => (s.order === "desc" ? `-${s.key}` : s.key))
        .join(",");

      params.sort = sortBy
        .map((s) => (s.order === "desc" ? `-${s.key}` : s.key))
        .join(",");
    }

    const response = await ipAddressResource.index(params);
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

function openEditDialog(item: IpAddress) {
  selectedIpAddress.value = item;
  showEditDialog.value = true;
}

function openDeleteDialog(item: IpAddress) {
  selectedIpAddress.value = item;
  showDeleteDialog.value = true;
}
</script>

<style scoped></style>
