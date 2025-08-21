<script setup lang="ts">
import DataTable from "@/components/common/data-table/Index.vue";
import PageHeader from "@/components/common/page/PageHeader.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { BreadcrumbItem, PageQuery, TableResponse, Transaction } from "@/types";
import { columns } from "./partials/column";
import { computed, provide, shallowRef } from "vue";
import FormDialog from "./partials/FormDialog.vue";
import {
  FieldOption,
  FilterRow,
} from "@/components/common/data-table/DataTableFilter.vue";

interface Props {
  transactions: TableResponse<Transaction>;
  query: PageQuery;
  filters: FilterRow[];
  filterSchema: FieldOption[];
  errors: { [key: string]: string | string[] | undefined };
}
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Transaction",
    href: "/transaction",
  },
];

const formDialogOpen = shallowRef<boolean>(false);
const pagination = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { data, ...rest } = props.transactions;
  return rest;
});

provide("pagination", pagination);
provide("filter-date", false);
provide("filter-schema", props.filterSchema);
provide("filters", props.filters);
provide("url", { index: "transaction.index", destroy: "transaction.multiple-destroy" });
provide("query", props.query);
</script>

<template>
  <FormDialog
    v-model:open="formDialogOpen"
    :header="{
      title: 'New Transaction',
      description: 'Complete this form to add new transaction.',
    }"
  />

  <AppLayout :breadcrumbs="breadcrumbs" :errors="errors">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader
        title="Transaction"
        description="list all of your transaction."
        add-button="Transaction"
        @click:add="formDialogOpen = true"
      />

      <DataTable :data="transactions.data" :columns="columns" />
    </div>
  </AppLayout>
</template>
