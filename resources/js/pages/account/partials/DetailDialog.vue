<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Separator } from "@/components/ui/separator";
import { formatIdr } from "@/lib/utils";
import { Account } from "@/types";
import { BanknoteArrowDown, BanknoteArrowUp } from "lucide-vue-next";
import { getTypeLabel, getIconType } from "./column";

interface Props {
  data: Account;
}

defineProps<Props>();
const open = defineModel<boolean>("open");
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
        {{ formatIdr(data.balance, true) }}
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
            {{ formatIdr(6000000, true) }}
          </p>
        </div>
        <div class="flex flex-col gap-2">
          <p class="text-destructive flex gap-1">
            <BanknoteArrowDown />
            Out
          </p>

          <p class="font-lato font-semibold tracking-wide">
            {{ formatIdr(550000, true) }}
          </p>
        </div>
      </div>

      <Separator />

      <h1 class="my-2 font-semibold">Account Details</h1>

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
          <p class="text-muted-foreground text-sm">Note</p>
          <p>{{ data.notes ?? "-" }}</p>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
