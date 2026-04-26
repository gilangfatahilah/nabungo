<script setup lang="ts">
import DataTable from "@/components/common/data-table/Index.vue";
import PageHeader from "@/components/common/page/PageHeader.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { BreadcrumbItem, Debt, PageQuery, TableResponse } from "@/types";
import { columns } from "./partials/column";
import { computed, provide, shallowRef } from "vue";
import FormDialog from "./partials/FormDialog.vue";
import {
  FieldOption,
  FilterRow,
} from "@/components/common/data-table/DataTableFilter.vue";

interface Props {
  debts: TableResponse<Debt>;
  query: PageQuery;
  filters: FilterRow[];
  filterSchema: FieldOption[];
  errors: { [key: string]: string | string[] | undefined };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Debt",
    href: "/debt",
  },
];

const formDialogOpen = shallowRef<boolean>(false);

const pagination = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { data, ...rest } = props.debts;
  return rest;
});

provide("pagination", pagination);
provide("filter-month", false);
provide("filter-date", false);
provide("filter-schema", props.filterSchema);
provide("filters", props.filters);
provide("url", { index: "debt.index", destroy: "debt.multiple-destroy" });
provide("query", props.query);
</script>

<template>
  <FormDialog
    v-model:open="formDialogOpen"
    :header="{
      title: 'New Debt / Receivable',
      description: 'Record a new debt or receivable entry.',
    }"
  />

  <AppLayout :breadcrumbs="breadcrumbs" :errors="errors">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader
        title="Debt & Receivable"
        description="Track all your debts and receivables."
        add-button="Debt"
        @click:add="formDialogOpen = true"
      />

      <DataTable :data="debts.data" :columns="columns" />
    </div>
  </AppLayout>
</template>
