<script setup lang="ts">
import { Dialog, DialogContent } from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { Separator } from "@/components/ui/separator";
import { formatIdr } from "@/lib/utils";
import { Goal } from "@/types";
import { getTypeLabel, getIconType } from "./column";

interface Props {
  data: Goal;
}

const { data } = defineProps<Props>();
const open = defineModel<boolean>("open");
</script>

<template>
  <Dialog :open="open" @update:open="open = $event">
    <DialogContent class="gap-1 max-w-sm">
      <h1 class="my-2 font-semibold text-muted-foreground">{{ data.title }}</h1>

      <div class="flex flex-col gap-4">
        <!-- Progress -->
        <div class="space-y-2">
          <p class="text-muted-foreground font-semibold">
            <span
              :class="[
                'md:text-3xl font-bold',
                data.progress < 100 ? 'text-primary' : 'text-[#f43f5e]',
              ]"
              >{{ data.progress }}%</span
            >
            of target saved
          </p>
          <Progress
            :model-value="data.progress"
            :bg-color="data.progress < 100 ? 'bg-primary' : 'bg-[#f43f5e]'"
          />
        </div>

        <Separator />

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Status</p>
          <Badge
            class="capitalize flex items-center gap-1 w-fit"
            :variant="getTypeLabel(data.status)"
          >
            <component :is="getIconType(data.status)" :size="14" class="mr-1" />
            {{ data.status }}
          </Badge>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <p class="text-muted-foreground text-sm">Saved</p>
            <p class="font-medium">{{ formatIdr(Number(data.saved_amount), true) }}</p>
          </div>
          <div class="space-y-1">
            <p class="text-muted-foreground text-sm">Target</p>
            <p class="font-medium">{{ formatIdr(Number(data.target_amount), true) }}</p>
          </div>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Deadline</p>
          <p>{{ data.deadline }}</p>
        </div>

        <div class="space-y-1">
          <p class="text-muted-foreground text-sm">Linked Account</p>
          <p>{{ data.account?.name ?? '-' }}</p>
        </div>

        <div v-if="data.notes" class="space-y-1">
          <p class="text-muted-foreground text-sm">Notes</p>
          <p class="text-sm">{{ data.notes }}</p>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
