<script setup lang="ts">
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Calendar } from "lucide-vue-next";
</script>

<template>
  <Select>
    <SelectTrigger>
      <Calendar />
      <SelectValue placeholder="Period" />
    </SelectTrigger>
    <SelectContent>
      <SelectGroup>
        <SelectLabel>Period</SelectLabel>
        <SelectItem value="7"> Last 7 Days </SelectItem>
        <SelectItem value="30"> Last 30 Days </SelectItem>
        <SelectItem value="90"> Last 3 Months </SelectItem>
      </SelectGroup>
    </SelectContent>
  </Select>
</template>
