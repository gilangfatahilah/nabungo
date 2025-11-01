<script setup lang="ts">
import { Account } from "@/types";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Edit, Eye, MoreVertical, Trash2 } from "lucide-vue-next";
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
import DetailDialog from "./DetailDialog.vue";

const { row } = defineProps<{
  row: Account;
}>();

const loading = ref(false);
const dialogOpen = ref({
  detail: false,
  edit: false,
  delete: false,
});

const handleDelete = () => {
  router.delete(route("account.destroy", row.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Success, data has successfully deleted.");
      dialogOpen.value.delete = false;
      loading.value = false;
    },
    onError: () => {
      toast.error("Failed, something went wrong, please try again.");

      dialogOpen.value.delete = false;
      loading.value = false;
    },
  });
};
</script>

<template>
  <!-- Detail Dialog -->
  <DetailDialog v-model:open="dialogOpen.detail" :data="row" />

  <!-- Edit Dialog -->
  <FormDialog
    v-model:open="dialogOpen.edit"
    :header="{
      title: 'Edit Account',
      description: 'You can update your account detail by completing this form.',
    }"
    :default-values="row"
  />

  <!-- Delete Dialog -->
  <ConfirmationDialog
    v-model:open="dialogOpen.delete"
    v-model:loading="loading"
    :description="`You will delete account ${row.name}, this action can't be undone.`"
    confirmation-label="Yes, Delete it"
    confirmation-button-variant="destructive"
    @confirm="handleDelete"
  />

  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" class="w-8 h-8 p-0">
        <span class="sr-only">Open menu</span>
        <MoreVertical class="w-4 h-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuLabel>Actions</DropdownMenuLabel>
      <DropdownMenuItem @click="dialogOpen.detail = true">
        <Eye class="mr-2" />
        Detail
      </DropdownMenuItem>
      <DropdownMenuItem @click="dialogOpen.edit = true">
        <Edit class="mr-2" />
        Edit
      </DropdownMenuItem>

      <template v-if="row.type !== 'goal'">
        <DropdownMenuSeparator />

        <DropdownMenuItem variant="destructive" @click="dialogOpen.delete = true">
          <Trash2 class="mr-2" />
          Delete
        </DropdownMenuItem>
      </template>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
