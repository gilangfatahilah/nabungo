<script setup lang="ts">
import DataTable from "@/components/common/data-table/Index.vue";
import PageHeader from "@/components/common/page/PageHeader.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { BreadcrumbItem, Category, PageQuery, TableResponse } from "@/types";
import { columns } from "./partials/column";
import { computed, provide, shallowRef } from "vue";
import FormDialog from "./partials/FormDialog.vue";

interface Props {
  categories: TableResponse<Category>;
  query: PageQuery;
  errors: { [key: string]: string | string[] | undefined };
}
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Category",
    href: "/category",
  },
];

const formDialogOpen = shallowRef<boolean>(false);
const pagination = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { data, ...rest } = props.categories;
  return rest;
});

provide("pagination", pagination);
provide("filter-date", false);
provide("url", { index: "category.index", destroy: "category.multiple-destroy" });
provide("query", props.query);
</script>

<template>
  <FormDialog
    v-model:open="formDialogOpen"
    :header="{
      title: 'New Category',
      description: 'Complete this form to add new category.',
    }"
  />

  <AppLayout :breadcrumbs="breadcrumbs" :errors="errors">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader
        title="Category"
        description="list all of your category."
        add-button="Category"
        @click:add="formDialogOpen = true"
      />

      <DataTable :data="categories.data" :columns="columns" />
    </div>
  </AppLayout>
</template>
