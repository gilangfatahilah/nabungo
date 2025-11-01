<script setup lang="ts">
import DataTable from "@/components/common/data-table/Index.vue";
import PageHeader from "@/components/common/page/PageHeader.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { BreadcrumbItem, Goal, PageQuery, TableResponse } from "@/types";
import { columns } from "./partials/column";
import { computed, provide, shallowRef } from "vue";
import FormDialog from "./partials/FormDialog.vue";
import {
  FieldOption,
  FilterRow,
} from "@/components/common/data-table/DataTableFilter.vue";

interface Props {
  goals: TableResponse<Goal>;
  query: PageQuery;
  filters: FilterRow[];
  filterSchema: FieldOption[];
  errors: { [key: string]: string | string[] | undefined };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Goal",
    href: "/goal",
  },
];

const formDialogOpen = shallowRef<boolean>(false);
const pagination = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { data, ...rest } = props.goals;
  return rest;
});

provide("pagination", pagination);
provide("filter-month", false);
provide("filter-date", false);
provide("filter-schema", props.filterSchema);
provide("filters", props.filters);
provide("url", { index: "goal.index", destroy: "goal.multiple-destroy" });
provide("query", props.query);
</script>

<template>
  <FormDialog
    v-model:open="formDialogOpen"
    :header="{
      title: 'New Goal',
      description: 'Complete this form to add new saving.',
    }"
  />

  <AppLayout :breadcrumbs="breadcrumbs" :errors="errors">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader
        title="Goal"
        description="list all of your saving."
        add-button="Goal"
        @click:add="formDialogOpen = true"
      />

      <DataTable :data="goals.data" :columns="columns" />
    </div>
  </AppLayout>
</template>
