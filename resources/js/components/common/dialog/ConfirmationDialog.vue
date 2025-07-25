<script setup lang="ts">
import { Button, ButtonVariants } from "@/components/ui/button";
import { LoaderCircle } from "lucide-vue-next";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";

interface Props {
  title?: string;
  description?: string;
  confirmationLabel?: string;
  confirmationButtonVariant?: ButtonVariants["variant"];
}

defineProps<Props>();
const open = defineModel<boolean>("open");
const loading = defineModel<boolean>("loading");

const emits = defineEmits<{
  confirm: [];
}>();
</script>

<template>
  <Dialog :open="open" @update:open="open = $event">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title ?? "Are you absolutely sure?" }}</DialogTitle>
        <DialogDescription>
          {{ description ?? "This action cannot be undone." }}
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <DialogClose>
          <Button variant="outline" size="sm"> Cancel </Button>
        </DialogClose>
        <Button
          :variant="confirmationButtonVariant ?? 'default'"
          size="sm"
          class="cursor-pointer"
          :disabled="loading"
          @click="emits('confirm')"
        >
          <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
          {{ confirmationLabel ?? "Continue" }}</Button
        >
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
