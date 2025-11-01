<script setup lang="ts">
import { ref, watch } from "vue";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Separator } from "@/components/ui/separator";
import { formatIdr } from "@/lib/utils";
import { Account } from "@/types";
import { BanknoteArrowDown, BanknoteArrowUp } from "lucide-vue-next";
import { getTypeLabel, getIconType } from "./column";
import { Button } from "@/components/ui/button";
import { router } from "@inertiajs/vue3";

interface Props {
  data: Account;
}

interface Summary {
  income: number;
  expense: number;
  formattedIncome: string;
  formattedExpense: string;
}

const { data } = defineProps<Props>();
const open = defineModel<boolean>("open");

const summary = ref<Summary | null>(null);
const summaryLoading = ref<boolean>(false);

watch(open, async (newVal) => {
  if (newVal) {
    summaryLoading.value = true;
    try {
      const response = await fetch(route("account.transaction-summary", { id: data.id }));
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const result = await response.json();

      console.log("Transaction Summary:", result);
      summary.value = result.data;
    } catch (error) {
      console.error("Failed to fetch transaction summary:", error);
    } finally {
      summaryLoading.value = false;
    }
  }
});

const toHistory = () => {
  summaryLoading.value = false;
  open.value = false;

  router.get(route("history.index"), {
    page: 1,
    per_page: 10,
    filters: [{ field: "account_id", operator: "=", value: data.id }],
  });
};
</script>

<template>
  <Dialog :open="open" @update:open="open = $event" class="max-w-[300px]">
    <DialogContent class="gap-1">
      <DialogHeader>
        <DialogTitle class="text-muted-foreground text-sm font-light"
          >Total Balance</DialogTitle
        >
      </DialogHeader>

      <h1 class="font-lato font-bold text-2xl tracking-wide mb-2">
        {{ formatIdr(Number(data.balance), true) }}
      </h1>

      <Separator />

      <h1 class="text-center mt-2 text-muted-foreground">Stats This Month</h1>

      <div class="flex py-2 px-6 items-center justify-between">
        <div class="flex flex-col gap-2">
          <p class="text-primary flex gap-1">
            <BanknoteArrowUp />
            In
          </p>

          <p class="font-lato font-semibold tracking-wide">
            {{
              summaryLoading
                ? "Loading..."
                : formatIdr(Number(summary?.income) ?? 0, true)
            }}
          </p>
        </div>
        <div class="flex flex-col gap-2">
          <p class="text-destructive flex gap-1">
            <BanknoteArrowDown />
            Out
          </p>

          <p class="font-lato font-semibold tracking-wide">
            {{
              summaryLoading
                ? "Loading..."
                : formatIdr(Number(summary?.expense) ?? 0, true)
            }}
          </p>
        </div>
      </div>

      <Separator />

      <div class="flex flex-col gap-4">
        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Name</p>
          <p>{{ data.name }}</p>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Type</p>
          <Badge :variant="getTypeLabel(data.type)" class="flex items-center gap-1">
            <component :is="getIconType(data.type)" :size="14" class="mr-1" />
            <p class="capitalize">{{ data.type }}</p>
          </Badge>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Opening Balance</p>
          <p>
            {{
              summaryLoading
                ? "Loading..."
                : formatIdr(
                    Number(data.balance) -
                      (Number(summary?.income) ?? 0) +
                      (Number(summary?.expense) ?? 0),
                    true
                  )
            }}
          </p>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Note</p>
          <p>{{ data.notes ?? "-" }}</p>
        </div>
      </div>

      <Separator class="my-2" />

      <Button variant="outline" size="sm" @click="toHistory">History</Button>
    </DialogContent>
  </Dialog>
</template>
