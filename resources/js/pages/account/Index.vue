<script setup lang="ts">
import DataTable from "@/components/common/data-table/Index.vue";
import PageHeader from "@/components/common/page/PageHeader.vue";
import AppLayout from "@/layouts/AppLayout.vue";

import { Account, BreadcrumbItem, PageQuery, TableResponse } from "@/types";
import { columns } from "./partials/column";
import { computed, provide, shallowRef } from "vue";
import FormDialog from "./partials/FormDialog.vue";

interface Props {
  accounts: TableResponse<Account>;
  query: PageQuery;
}
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Account",
    href: "/account",
  },
];

const formDialogOpen = shallowRef<boolean>(false);
const pagination = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { data, ...rest } = props.accounts;
  return rest;
});

provide("pagination", pagination);
provide("filter-date", false);
provide("url", { index: "account.index", destroy: "account.multiple-destroy" });
provide("query", props.query);
</script>

<template>
  <FormDialog
    v-model:open="formDialogOpen"
    :header="{
      title: 'New Account',
      description: 'Complete this form to add new account.',
    }"
  />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader
        title="Account"
        description="list all of your account."
        add-button="Account"
        @click:add="formDialogOpen = true"
      />

      <DataTable :data="accounts.data" :columns="columns" />
    </div>
  </AppLayout>
</template>
