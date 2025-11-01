<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";
import { Separator } from "@/components/ui/separator";
import { ScrollArea } from "@/components/ui/scroll-area";
import { ActivityLog } from "@/types";
import { Info } from "lucide-vue-next";
import { getIconType, getTypeLabel } from "./column";

interface Props {
  log: ActivityLog;
}

defineProps<Props>();

function formatSubject(str: string): string {
  const parts = str.split("\\");

  return parts[parts.length - 1];
}

const formatDate = (date: string) => {
  const parsedDate = new Date(date);

  return Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(parsedDate);
};
</script>

<template>
  <Dialog>
    <DialogTrigger as-child>
      <Info :size="14" class="ml-4 cursor-pointer text-muted-foreground" />
    </DialogTrigger>

    <DialogContent class="max-w-lg p-6">
      <div class="mb-4">
        <p class="text-xl text-center font-semibold">Activity Log Detail</p>
        <Separator class="mt-2" />
      </div>

      <ScrollArea class="pr-2">
        <div class="flex flex-col gap-4">
          <div class="flex justify-between items-center">
            <p class="text-muted-foreground text-sm">Created at</p>
            <p class="text-sm">{{ formatDate(log.created_at) }}</p>
          </div>

          <div class="flex justify-between items-center">
            <p class="text-muted-foreground text-sm">Description</p>
            <p class="text-sm">{{ log.description }}</p>
          </div>

          <div class="flex justify-between items-center">
            <p class="text-muted-foreground text-sm">Log Name</p>
            <p class="text-sm capitalize">{{ log.log_name }}</p>
          </div>

          <div class="flex justify-between items-center">
            <p class="text-muted-foreground text-sm">Subject</p>
            <Badge variant="secondary">
              {{ formatSubject(log.subject_type as ActivityLog['subject_type'] ?? "-") }}
            </Badge>
          </div>

          <div class="flex justify-between items-center">
            <p class="text-muted-foreground text-sm">Subject</p>
            <Badge :variant="getTypeLabel(log.event)">
              <component :is="getIconType(log.event)"></component>
              <p class="capitalize">{{ log.event }}</p>
            </Badge>
          </div>

          <div>
            <p class="text-muted-foreground text-sm mb-1">Changes</p>
            <div class="border rounded-md p-3 text-sm bg-muted/40">
              <div v-if="log.properties?.old">
                <p class="font-semibold mb-1">Old Values:</p>
                <ul class="list-disc pl-5">
                  <li v-for="(val, key) in log.properties.old" :key="key" class="mb-1">
                    <span class="font-medium capitalize">{{ key.split(".")[0] }} :</span>
                    {{ val }}
                  </li>
                </ul>
              </div>

              <div v-if="log.properties?.attributes" class="mt-3">
                <p class="font-semibold mb-1">New Values:</p>
                <ul class="list-disc pl-5">
                  <li
                    v-for="(val, key) in log.properties.attributes"
                    :key="key"
                    class="mb-1"
                  >
                    <span class="font-medium capitalize">{{ key.split(".")[0] }} :</span>
                    {{ val }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </ScrollArea>
    </DialogContent>
  </Dialog>
</template>
