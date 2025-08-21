<script setup lang="ts">
import DataTable from "@/components/common/data-table/Index.vue";
import PageHeader from "@/components/common/page/PageHeader.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { BreadcrumbItem, Budget, PageQuery, TableResponse } from "@/types";
import { columns } from "./partials/column";
import { computed, provide, shallowRef } from "vue";
import FormDialog from "./partials/FormDialog.vue";

interface Props {
  budgets: TableResponse<Budget>;
  query: PageQuery;
  errors: { [key: string]: string | string[] | undefined };
}
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Budget",
    href: "/budget",
  },
];

const formDialogOpen = shallowRef<boolean>(false);
const pagination = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { data, ...rest } = props.budgets;
  return rest;
});

provide("pagination", pagination);
provide("filter-month", true);
provide("url", { index: "budget.index", destroy: "budget.multiple-destroy" });
provide("query", props.query);
</script>

<template>
  <FormDialog
    v-model:open="formDialogOpen"
    :header="{
      title: 'New Budget',
      description: 'Complete this form to add new budget.',
    }"
  />

  <AppLayout :breadcrumbs="breadcrumbs" :errors="errors">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader
        title="Budget"
        description="list all of your budget."
        add-button="Budget"
        @click:add="formDialogOpen = true"
      />

      <DataTable :data="budgets.data" :columns="columns" />
    </div>
  </AppLayout>
</template>
