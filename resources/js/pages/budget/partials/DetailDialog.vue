<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent } from "@/components/ui/dialog";
import { Progress } from "@/components/ui/progress";
import { dateToMonth, formatIdr } from "@/lib/utils";
import { Budget } from "@/types";
import { cn } from "@/lib/utils";
import { Separator } from "@/components/ui/separator";
import { CircleAlert, CircleCheck } from "lucide-vue-next";

interface Props {
  data: Budget;
}

const { data } = defineProps<Props>();
const open = defineModel<boolean>("open");
</script>

<template>
  <Dialog :open="open" @update:open="open = $event" class="max-w-[300px]">
    <DialogContent class="gap-1">
      <h1 class="my-2 font-semibold text-muted-foreground">
        {{ data.category.name }} Budget Details
      </h1>

      <div class="flex flex-col gap-4">
        <div class="space-y-2">
          <p class="text-muted-foreground font-semibold">
            <span
              :class="
                cn(
                  'md:text-3xl font-bold text-primary',
                  data.usage < 100 ? 'text-primary' : 'text-destructive'
                )
              "
              >{{ data.usage }}%</span
            >
            of budget used
          </p>
          <Progress
            :model-value="data.usage"
            :bg-color="data.usage < 100 ? 'bg-primary' : 'bg-[#f43f5e]'"
          />
        </div>

        <Separator />

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Month</p>
          <p>{{ dateToMonth(data.month) }}</p>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Status</p>
          <Badge
            class="capitalize flex items-center gap-1"
            :variant="data.usage <= 100 ? 'a-success' : 'a-error'"
          >
            <component :is="data.usage <= 100 ? CircleCheck : CircleAlert"></component>

            {{ data.usage < 100 ? "In Budget" : "Budget Exceeded" }}
          </Badge>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Expense this month</p>
          {{ formatIdr(Number(data.total_expense) ?? 0, true) }}
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Monthly Budget</p>
          {{ formatIdr(Number(data.amount), true) }}
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
