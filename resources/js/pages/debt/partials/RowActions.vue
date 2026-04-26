<script setup lang="ts">
import { Debt } from "@/types";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Edit, Eye, MoreVertical, Trash2, Wallet } from "lucide-vue-next";

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import ConfirmationDialog from "@/components/common/dialog/ConfirmationDialog.vue";
import FormDialog from "./FormDialog.vue";
import PaymentDialog from "./PaymentDialog.vue";
import DetailDialog from "./DetailDialog.vue";

const { row } = defineProps<{ row: Debt }>();

const loading = ref(false);
const dialogOpen = ref({
  detail:  false,
  edit:    false,
  pay:     false,
  delete:  false,
});

const handleDelete = () => {
  loading.value = true;
  router.delete(route("debt.destroy", row.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Hutang/piutang berhasil dihapus.");
      dialogOpen.value.delete = false;
      loading.value = false;
    },
    onError: () => {
      toast.error("Gagal menghapus. Coba lagi.");
      dialogOpen.value.delete = false;
      loading.value = false;
    },
  });
};
</script>

<template>
  <!-- Dialogs -->
  <DetailDialog  v-model:open="dialogOpen.detail" :data="row" />
  <FormDialog
    v-model:open="dialogOpen.edit"
    :header="{ title: 'Edit Debt', description: 'Update debt/receivable details.' }"
    :default-values="row"
  />
  <PaymentDialog v-model:open="dialogOpen.pay" :debt="row" />
  <ConfirmationDialog
    v-model:open="dialogOpen.delete"
    title="Delete Debt"
    :description="`Are you sure you want to delete '${row.title}'? All payment records will also be removed.`"
    :loading="loading"
    @confirm="handleDelete"
  />

  <!-- Trigger -->
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" class="h-7 w-7">
        <MoreVertical :size="16" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuLabel>Actions</DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuItem @click="dialogOpen.detail = true">
        <Eye :size="14" class="mr-2" /> View Detail
      </DropdownMenuItem>
      <DropdownMenuItem @click="dialogOpen.edit = true" :disabled="row.status === 'paid'">
        <Edit :size="14" class="mr-2" /> Edit
      </DropdownMenuItem>
      <DropdownMenuItem @click="dialogOpen.pay = true" :disabled="row.status === 'paid'">
        <Wallet :size="14" class="mr-2" /> Record Payment
      </DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem
        class="text-destructive focus:text-destructive"
        @click="dialogOpen.delete = true"
      >
        <Trash2 :size="14" class="mr-2" /> Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
