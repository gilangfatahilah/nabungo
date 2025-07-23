<script setup lang="ts">
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { CurveType } from "@unovis/ts";
// import { AreaChart } from "@/components/ui/chart-area";
import { Badge } from "@/components/ui/badge";
import { TrendingDown } from "lucide-vue-next";
import { formatIdr } from "@/lib/utils";
import { AreaChart } from "@/components/ui/chart-area";

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
  <Card class="h-full pt-4 pb-0 !gap-2">
    <CardHeader>
      <div class="flex justify-between items-center pb-0">
        <CardTitle class="font-normal text-sm text-muted-foreground"
          >Total Balance</CardTitle
        >

        <Badge variant="outline" class="rounded-sm">
          <TrendingDown class="mr-1 w-4 h-4" />
          12%
        </Badge>
      </div>
    </CardHeader>
    <CardContent>
      <h1 class="font-bold font-lato text-xl tracking-wide">
        IDR {{ formatIdr(12000000) }}
      </h1>
      <span class="font-light text-xs text-muted-foreground">+12% vs Last month.</span>
    </CardContent>
    <CardFooter class="p-0">
      <AreaChart
        class="h-12 w-full"
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
    </CardFooter>
  </Card>
</template>
