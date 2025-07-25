<script setup lang="ts">
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-nocheck

import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Link, router } from "@inertiajs/vue3";
import type { Table } from "@tanstack/vue-table";
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronsLeftIcon,
  ChevronsRightIcon,
} from "lucide-vue-next";
import { inject, onMounted } from "vue";

interface DataTablePaginationProps<TData> {
  table: Table<TData>;
}

const props = defineProps<DataTablePaginationProps<any>>();

/**
 * Check if page size exist in params
 * if yes then set table page size.
 */
onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const pageSize = urlParams.get("per_page") ?? undefined;

  if (pageSize) {
    props.table.setPageSize(pageSize);
  }
});

const pageSizes = [10, 20, 30, 40, 50];

const data = inject("pagination");
const url = inject("url");
const query = inject("query");

const handlePageSizeChange = (newSize) => {
  props.table.setPageSize(Number(newSize));

  router.get(
    route(url.index, {
      page: 1,
      per_page: newSize,
      search: query?.search ?? "",
    })
  );
};
</script>

<template>
  <div class="flex items-center justify-between px-2">
    <div class="hidden md:block flex-1 text-sm text-muted-foreground">
      Total : {{ data.total }} Rows.
    </div>
    <div class="flex items-center space-x-6 lg:space-x-8">
      <div class="flex items-center space-x-2">
        <p class="text-sm font-medium">Rows per page</p>
        <Select
          :model-value="`${data.per_page}`"
          @update:model-value="handlePageSizeChange"
        >
          <SelectTrigger class="h-8 w-[70px]">
            <SelectValue :placeholder="`${data.per_page}`" />
          </SelectTrigger>
          <SelectContent side="top">
            <SelectItem
              v-for="pageSize in pageSizes"
              :key="pageSize"
              :value="`${pageSize}`"
            >
              {{ pageSize }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div class="flex w-[100px] items-center justify-center text-sm font-medium">
        Page {{ data.current_page }} of
        {{ data.last_page }}
      </div>
      <div class="flex items-center space-x-2">
        <Link :href="data.first_page_url ?? '#'" :disabled="!data.first_page_url">
          <Button
            variant="outline"
            class="hidden h-8 w-8 p-0 lg:flex"
            :disabled="!data.first_page_url"
          >
            <span class="sr-only">Go to first page</span>
            <ChevronsLeftIcon class="h-4 w-4" />
          </Button>
        </Link>

        <Link :href="data.prev_page_url ?? '#'" :disabled="!data.prev_page_url">
          <Button variant="outline" class="h-8 w-8 p-0" :disabled="!data.prev_page_url">
            <span class="sr-only">Go to previous page</span>
            <ChevronLeftIcon class="h-4 w-4" />
          </Button>
        </Link>

        <Link :href="data.next_page_url ?? '#'" :disabled="!data.next_page_url">
          <Button variant="outline" class="h-8 w-8 p-0" :disabled="!data.next_page_url">
            <span class="sr-only">Go to next page</span>
            <ChevronRightIcon class="h-4 w-4" />
          </Button>
        </Link>

        <Link :href="data.last_page_url ?? '#'" :disabled="!data.last_page_url">
          <Button
            variant="outline"
            class="hidden h-8 w-8 p-0 lg:flex"
            :disabled="!data.last_page_url"
          >
            <span class="sr-only">Go to last page</span>
            <ChevronsRightIcon class="h-4 w-4" />
          </Button>
        </Link>
      </div>
    </div>
  </div>
</template>
