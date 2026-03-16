<script setup lang="ts">
import { Goal } from "@/types";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Ban, Edit, Eye, MoreVertical, Trash2 } from "lucide-vue-next";
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
  row: Goal;
}>();

const loading = ref(false);
const dialogOpen = ref({
  detail: false,
  edit: false,
  cancel: false,
  delete: false,
});

const handleCancel = () => {
  loading.value = true;
  router.patch(route("goal.cancel", row.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Goal has been cancelled.");
      dialogOpen.value.cancel = false;
      loading.value = false;
    },
    onError: () => {
      toast.error("Failed, something went wrong, please try again.");
      dialogOpen.value.cancel = false;
      loading.value = false;
    },
  });
};

const handleDelete = () => {
  loading.value = true;
  router.delete(route("goal.destroy", row.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Success, goal has been deleted.");
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
      title: 'Edit Goal',
      description: 'Update your saving goal details.',
    }"
    :default-values="row"
  />

  <!-- Cancel Dialog -->
  <ConfirmationDialog
    v-model:open="dialogOpen.cancel"
    v-model:loading="loading"
    :description="`You are about to cancel the goal '${row.title}'. The goal data will be kept but marked as cancelled.`"
    confirmation-label="Yes, Cancel it"
    confirmation-button-variant="destructive"
    @confirm="handleCancel"
  />

  <!-- Delete Dialog -->
  <ConfirmationDialog
    v-model:open="dialogOpen.delete"
    v-model:loading="loading"
    :description="`You will permanently delete the goal '${row.title}' and all related transactions. This action cannot be undone.`"
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

      <DropdownMenuSeparator />

      <DropdownMenuItem
        v-if="row.status === 'ongoing'"
        variant="destructive"
        @click="dialogOpen.cancel = true"
      >
        <Ban class="mr-2" />
        Cancel Goal
      </DropdownMenuItem>

      <DropdownMenuItem variant="destructive" @click="dialogOpen.delete = true">
        <Trash2 class="mr-2" />
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
