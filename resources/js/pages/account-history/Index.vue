<script setup lang="ts">
import DataTable from "@/components/common/data-table/Index.vue";
import PageHeader from "@/components/common/page/PageHeader.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { AccountHistory, BreadcrumbItem, PageQuery, TableResponse } from "@/types";
import { columns } from "./partials/column";
import { computed, provide } from "vue";
import {
  FieldOption,
  FilterRow,
} from "@/components/common/data-table/DataTableFilter.vue";

interface Props {
  histories: TableResponse<AccountHistory>;
  query: PageQuery;
  filters: FilterRow[];
  filterSchema: FieldOption[];
  errors: { [key: string]: string | string[] | undefined };
}
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "History",
    href: "/history",
  },
];

const pagination = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { data, ...rest } = props.histories;
  return rest;
});

provide("pagination", pagination);
provide("filter-date", false);
provide("filter-schema", props.filterSchema);
provide("filters", props.filters);
provide("url", { index: "history.index" });
provide("query", props.query);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :errors="errors">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader title="History" description="History of all transactions" />

      <DataTable :data="histories.data" :columns="columns" />
    </div>
  </AppLayout>
</template>
