<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";
import { Separator } from "@/components/ui/separator";
import { formatIdr } from "@/lib/utils";
import { AccountHistory } from "@/types";
import { Info } from "lucide-vue-next";

interface Props {
  data: AccountHistory;
}

defineProps<Props>();

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
  <Dialog class="max-w-[300px]">
    <DialogTrigger as-child>
      <Info :size="14" class="ml-4" />
    </DialogTrigger>

    <DialogContent class="gap-1">
      <div class="mt-2 mb-4 space-y-2">
        <p class="text-xl text-center font-semibold">History detail</p>

        <Separator />
      </div>

      <div class="flex flex-col gap-4">
        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Account</p>
          <Badge variant="secondary" class="capitalize rounded-sm">
            {{ data.account.name }}
          </Badge>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Date</p>
          <p>{{ formatDate(data.transaction.transaction_date) }}</p>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Amount</p>
          <p>{{ formatIdr(Number(data.amount), true) }}</p>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Type</p>
          <Badge
            :variant="data.type === 'in' ? 'a-success' : 'a-error'"
            class="capitalize rounded-sm"
          >
            {{ data.type }}
          </Badge>
        </div>

        <div class="space-y-1 flex justify-between items-center">
          <p class="text-muted-foreground text-sm">Balance</p>
          <p>
            {{
              formatIdr(Number(data.balance_before), true) +
              "->" +
              formatIdr(Number(data.balance_after), true)
            }}
          </p>
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
