<script setup lang="ts">
import { AreaChart } from "@/components/ui/chart-area";
import { CurveType } from "@unovis/ts";
import CustomChartTooltip from "./CustomChartTooltip.vue";

const data = [
  {
    date: "Jan",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Feb",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Mar",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Apr",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "May",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Jun",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Jul",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Aug",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Sep",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Oct",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Nov",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
  {
    date: "Dec",
    income: Math.floor(Math.random() * 2000) + 1000,
    outcome: Math.floor(Math.random() * 2000) + 1000,
  },
];
</script>

<template>
  <AreaChart
    :data="data"
    index="date"
    :categories="['income', 'outcome']"
    :colors="['#10b981', '#f43f5e']"
    :curve-type="CurveType.Natural"
    :show-legend="false"
    :show-grid-line="false"
    :custom-tooltip="CustomChartTooltip"
  />
</template>
