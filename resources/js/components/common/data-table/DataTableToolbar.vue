<script setup lang="ts">
import { Button } from "@/components/ui/button";
import type { Table } from "@tanstack/vue-table";

import { Input } from "@/components/ui/input";
import { CircleX, Search, Trash2 } from "lucide-vue-next";
import { computed, inject, ref } from "vue";
// import { priorities, statuses } from '../../../Pages/Role/data/data'
// import DataTableFacetedFilter from './DataTableFacetedFilter.vue'
import { router } from "@inertiajs/vue3";
import { CalendarDate, parseDate } from "@internationalized/date";
import DataTableViewOptions from "./DataTableViewOptions.vue";
import DateRangePicker from "@/components/DateRangePicker.vue";
import ConfirmationDialog from "../dialog/ConfirmationDialog.vue";
import { toast } from "vue-sonner";

interface DataTableToolbarProps<TData> {
  table: Table<TData>;
}

interface QueryParams {
  search: string;
  page: number;
  per_page: number;
  start_date: string;
  end_date: string;
}

const props = defineProps<DataTableToolbarProps<any>>();

const isFiltered = computed(() => props.table.getState().columnFilters.length > 0);

const url = inject("url") as { index: string; destroy: string };
const query = inject("query") as QueryParams;
const filterDate = inject("filter-date") ?? false;

const today = new CalendarDate(
  new Date().getFullYear(),
  new Date().getMonth() + 1,
  new Date().getDate()
);

const selectedRow = computed(() => props.table.getSelectedRowModel());

const loading = ref<boolean>(false);
const confirmationDialogOpen = ref<boolean>(false);
const search = ref(query?.search ?? "");
const range = ref({
  start: query.start_date ? parseDate(query.start_date) : today,
  end: query.end_date ? parseDate(query.end_date) : today.add({ days: 7 }),
});

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

const handleDateFilter = () => {
  router.get(
    route(url.index, {
      page: query.page ?? 1,
      per_page: query.per_page ?? 10,
      start_date: range.value.start.toString(),
      end_date: range.value.end.toString(),
    })
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

      <!-- <DataTableFacetedFilter
        v-if="table.getColumn('status')"
        :column="table.getColumn('status')"
        title="Status"
        :options="statuses"
      />
      <DataTableFacetedFilter
        v-if="table.getColumn('priority')"
        :column="table.getColumn('priority')"
        title="Priority"
        :options="priorities"
      /> -->

      <DateRangePicker
        v-if="filterDate"
        v-model="range"
        @popover:close="handleDateFilter"
      />

      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="table.resetColumnFilters()"
      >
        Reset
        <CircleX class="ml-2 h-4 w-4" />
      </Button>
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
</template>
