<script setup lang="ts">
import { Button } from "@/components/ui/button";
import type { Table } from "@tanstack/vue-table";

import { Input } from "@/components/ui/input";
import { FunnelPlus, FunnelX, Search, Trash2 } from "lucide-vue-next";
import { computed, inject, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import DataTableViewOptions from "./DataTableViewOptions.vue";
import ConfirmationDialog from "../dialog/ConfirmationDialog.vue";
import { toast } from "vue-sonner";
import MonthRange from "../popover/MonthRange.vue";
import DataTableFilter, { FieldOption, FilterRow } from "./DataTableFilter.vue";

interface DataTableToolbarProps<TData> {
  table: Table<TData>;
}

export interface QueryParams {
  search: string;
  page: number;
  per_page: number;
  start_date: string;
  start_month: string;
  end_date: string;
  end_month: string;
}

const props = defineProps<DataTableToolbarProps<any>>();

/**
 * Injected properties
 */
const url = inject("url") as { index: string; destroy: string };
const query = inject("query") as QueryParams;
const filterSchema = inject("filter-schema") as FieldOption[];
const defaultFilters = inject("filters") as FilterRow[] | undefined;

/**
 * Variables section
 */
const selectedRow = computed(() => props.table.getSelectedRowModel());
const loading = ref<boolean>(false);
const confirmationDialogOpen = ref<boolean>(false);
const search = ref(query?.search ?? "");
const showFilter = ref<boolean>(false);
const filters = ref<FilterRow[]>(defaultFilters ?? []);
const resetFilters = ref<boolean>(false);

/**
 * request handling section
 */

const handleReset = () => {
  search.value = "";
  filters.value = [];
  resetFilters.value = true;

  router.get(
    route(url.index, {
      page: query.page ?? 1,
      per_page: query.per_page ?? 10,
    }),
    {},
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => {
        resetFilters.value = false;
      },
    }
  );
};

const handleSearch = (event: KeyboardEvent) => {
  const target = event.target as HTMLInputElement;

  router.get(
    route(url.index, {
      page: query.page ?? 1,
      per_page: query.per_page ?? 10,
      search: target.value,
    })
  );
};

const handleFilter = () => {
  if (filters.value.length === 0) {
    toast.error("Please add at least one filter.");
    return;
  }

  const mappedFilters = filters.value.map((filter) => {
    const { id, ...rest } = filter;
    return rest;
  });

  router.get(
    route(url.index),
    {
      page: query.page ?? 1,
      per_page: query.per_page ?? 10,
      filters: mappedFilters,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

const handleDelete = () => {
  const ids = selectedRow.value.rows.map((row) => row.original.id);
  loading.value = true;

  router.delete(route(url.destroy), {
    data: { ids },
    preserveScroll: true,
    onSuccess: () => {
      props.table.resetRowSelection();
      toast.success("Success, data has successfully deleted.");

      confirmationDialogOpen.value = false;
      loading.value = false;
    },
    onError: () => {
      props.table.resetRowSelection();
      toast.error("Failed, something went wrong, please try again.");

      confirmationDialogOpen.value = false;
      loading.value = false;
    },
  });
};

onMounted(() => {
  if (defaultFilters && defaultFilters.length > 0) {
    console.log("defaultFilters", defaultFilters);
    showFilter.value = true;
  }
});
</script>

<template>
  <ConfirmationDialog
    v-model:open="confirmationDialogOpen"
    v-model:loading="loading"
    :description="`You will delete all ${selectedRow.rows.length} rows you've selected, this action can't be undone.`"
    confirmation-label="Yes, Delete it"
    confirmation-button-variant="destructive"
    @confirm="handleDelete"
  />

  <div class="flex items-center justify-between gap-3">
    <div class="flex flex-1 items-center space-x-2">
      <div className="w-full lg:max-w-[350px] group">
        <div className="relative">
          <Input
            type="text"
            placeholder="Type something..."
            :model-value="search"
            class="h-8 pl-10 group-hover:bg-accent group-hover:placeholder:text-accent-foreground"
            @keydown.enter="handleSearch"
          />
          <Search
            class="absolute left-3 top-1/2 -translate-y-1/2 transform text-muted-foreground group-hover:text-accent-foreground"
            :size="18"
          />
          <span
            class="absolute right-2 top-1/2 -translate-y-1/2 transform rounded border bg-muted px-1.5 text-sm font-light text-muted-foreground group-hover:bg-muted-foreground group-hover:text-accent-foreground"
          >
            Enter
          </span>
        </div>
      </div>

      <Button
        size="sm"
        variant="outline"
        class="cursor-pointer"
        @click="showFilter = !showFilter"
      >
        <FunnelPlus v-if="!showFilter" class="w-4 h-4" />
        <FunnelX v-else class="w-4 h-4" />
        Filter
      </Button>

      <!-- <MonthRange v-if="filterMonth" /> -->
    </div>

    <div class="flex items-center gap-2">
      <Button
        size="sm"
        variant="destructive"
        class="cursor-pointer"
        v-if="selectedRow.rows.length"
        @click="confirmationDialogOpen = true"
      >
        <Trash2 />
        Delete
      </Button>

      <DataTableViewOptions :table="table" />
    </div>
  </div>

  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-300 ease-in-out"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <DataTableFilter
      v-if="showFilter"
      v-model="filters"
      :fields="filterSchema"
      @clear="handleReset"
      @apply="handleFilter"
      show-validation-errors
    />
  </Transition>

  <!-- <pre class="text-emerald-500 bg-card rounded-md p-4">
Filter Value :
{{ filters }}</pre> -->
</template>
