<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent  } from "@/components/ui/dialog";
import { formatIdr } from "@/lib/utils";
import { Category } from "@/types";
import { ArrowDownCircle, ArrowUpCircle } from "lucide-vue-next";
import { getTypeLabel } from "./column";

interface Props {
  data: Category;
}

defineProps<Props>();
const open = defineModel<boolean>("open");
</script>

<template>
  <Dialog :open="open" @update:open="open = $event" class="max-w-[300px]">
    <DialogContent class="gap-1">
      <h1 class="my-2 font-semibold">Category Details</h1>

      <div class="flex flex-col gap-4">
        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Name</p>
          <p>{{ data.name }}</p>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Type</p>
          <Badge :variant="getTypeLabel(data.type)" class="flex items-center gap-1">
            <component
              :is="data.type === 'income' ? ArrowDownCircle : ArrowUpCircle"
              :size="14"
              class="mr-1"
            />
            <p class="capitalize">{{ data.type }}</p>
          </Badge>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">
            <span class="capitalize">{{ data.type }}</span> this month
          </p>
          <p>{{ formatIdr(900000, true) }}</p>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
