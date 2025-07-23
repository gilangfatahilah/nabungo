<script setup lang="ts">
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { CurveType } from "@unovis/ts";
import { formatIdr } from "@/lib/utils";
import { AreaChart } from "@/components/ui/chart-area";
import { Button } from "@/components/ui/button";

const data = [
  { name: "Jan", total: Math.floor(Math.random() * 2000) + 1000 },
  { name: "Feb", total: Math.floor(Math.random() * 2000) + 1000 },
  { name: "Mar", total: Math.floor(Math.random() * 2000) + 1000 },
  { name: "Mar", total: Math.floor(Math.random() * 2000) + 1000 },
  { name: "Mar", total: Math.floor(Math.random() * 2000) + 1000 },
  { name: "Mar", total: Math.floor(Math.random() * 2000) + 1000 },
];
</script>

<template>
  <Card class="h-full py-2 !gap-2">
    <CardHeader>
      <div class="flex justify-between items-center pb-0">
        <CardTitle class="font-normal text-muted-foreground"> Balance </CardTitle>

        <Button variant="ghost" size="sm" class="font-semibold text-sm cursor-pointer"
          >View More</Button
        >
      </div>
    </CardHeader>
    <CardContent>
      <div class="flex justify-between max-h-20">
        <div class="flex flex-col justify-center">
          <h1 class="font-bold font-lato text-4xl tracking-wide">
            IDR {{ formatIdr(12000000) }}
          </h1>
        </div>

        <AreaChart
          class="max-h-20 w-1/3"
          index="name"
          :data="data"
          :categories="['total']"
          :colors="['#10b981']"
          :show-legend="false"
          :show-x-axis="false"
          :show-y-axis="false"
          :show-tooltip="false"
          :curve-type="CurveType.CatmullRom"
        />
      </div>
    </CardContent>

    <CardFooter>
      <p class="text-muted-foreground text-sm">
        You've increased your balance by
        <span class="text-primary">+IDR 200.000</span> this month.
      </p>
    </CardFooter>
  </Card>
</template>
