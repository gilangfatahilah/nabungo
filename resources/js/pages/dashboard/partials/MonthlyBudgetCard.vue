<script setup lang="ts">
import { ref } from "vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";

type DataItem = {
  label: string;
  value: number;
};

const data = ref<DataItem[]>([
  { label: "Lifestyle", value: 66 },
  { label: "Food", value: 40 },
  { label: "Transport", value: 180 },
  { label: "Utilities", value: 55 },
  { label: "Entertainment", value: 70 },
  { label: "Health", value: 35 },
  { label: "Education", value: 50 },
  { label: "Shopping", value: 60 },
  { label: "Travel", value: 45 },
  { label: "Others", value: 30 },
]);
</script>

<template>
  <Card class="h-full py-2 !gap-2">
    <CardHeader>
      <div class="flex justify-between items-center pb-0">
        <CardTitle class="font-normal text-muted-foreground text-sm">
          Monthly Budget
        </CardTitle>

        <Button variant="ghost" size="sm" class="font-semibold text-sm cursor-pointer">
          View More
        </Button>
      </div>
    </CardHeader>
    <CardContent>
      <ScrollArea class="h-[32vh]">
        <div v-for="(item, idx) in data" :key="idx" class="my-2 space-y-1">
          <p class="text-sm text-muted-foreground">
            {{ item.label }}
            <span class="float-right text-xs font-semibold text-foreground"
              >{{ item.value }}%</span
            >
          </p>
          <Progress
            :bg-color="item.value <= 100 ? 'bg-primary' : 'bg-[#f43f5e]'"
            :model-value="item.value"
          />
        </div>
      </ScrollArea>
    </CardContent>
  </Card>
</template>
