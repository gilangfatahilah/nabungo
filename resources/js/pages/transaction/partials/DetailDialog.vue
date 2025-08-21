<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent } from "@/components/ui/dialog";
import { formatIdr } from "@/lib/utils";
import { Transaction } from "@/types";
import { getTypeLabel } from "./column";
import { ArrowLeftRight, ArrowUpCircle, ArrowDownCircle } from "lucide-vue-next";
import Separator from "@/components/ui/separator/Separator.vue";

interface Props {
  data: Transaction;
}

defineProps<Props>();
const open = defineModel<boolean>("open");

const formatDate = (date: string) => {
  const parsedDate = new Date(date);

  return Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  }).format(parsedDate);
};
</script>

<template>
  <Dialog :open="open" @update:open="open = $event" class="max-w-[300px]">
    <DialogContent class="gap-1">
      <div class="flex flex-col gap-4">
        <Badge :variant="getTypeLabel(data.type)" class="flex items-center gap-1 mx-auto">
          <component
            :is="
              data.type === 'income'
                ? ArrowDownCircle
                : data.type === 'expense'
                ? ArrowUpCircle
                : ArrowLeftRight
            "
            :size="14"
            class="mr-1"
          />
          <p class="capitalize">{{ data.type }}</p>
        </Badge>

        <div class="space-y-1">
          <p class="text-center text-xl font-semibold">
            {{ formatIdr(data.amount, true) }}
          </p>
        </div>

        <Separator />

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Date</p>
          <p>{{ formatDate(data.transaction_date) }}</p>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Account</p>
          <Badge variant="secondary" class="capitalize rounded-sm">
            {{ data.account.name }}
          </Badge>
        </div>

        <div
          v-if="data.type === 'transfer'"
          class="space-y-1 flex justify-between items-center"
        >
          <p class="text-muted-foreground text-sm">To Account</p>
          <Badge variant="secondary" class="capitalize rounded-sm">
            {{ data.account_target?.name ?? "-" }}
          </Badge>
        </div>

        <div
          v-if="data.type !== 'transfer'"
          class="space-y-1 flex justify-between items-center"
        >
          <p class="text-muted-foreground text-sm">Category</p>
          <p>{{ data.category?.name ?? "-" }}</p>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Description</p>
          <p>{{ data.description }}</p>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Created at</p>
          <p>{{ formatDate(data.created_at) }}</p>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Updated at</p>
          <p>{{ formatDate(data.updated_at) }}</p>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
