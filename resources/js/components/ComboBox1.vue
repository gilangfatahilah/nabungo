<script setup lang="ts">
import { Check, ChevronsUpDown, Search } from "lucide-vue-next";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxItemIndicator,
  ComboboxList,
  ComboboxTrigger,
} from "@/components/ui/combobox";
import { VNode, isVNode, computed } from "vue";

type Option = {
  label: string | VNode;
  value: string | number;
};

const { options } = defineProps<{
  options: Option[];
}>();

const loading = defineModel<boolean>("loading");

const selectedValue = defineModel<string | number | undefined>("value");
const selectedOption = computed(() => {
  return options.find((opt) => opt.value === selectedValue.value);
});
</script>

<template>
  <Combobox
    by="label"
    :disabled="loading ?? false"
    @update:open="(val) => console.log(val)"
  >
    <ComboboxAnchor as-child>
      <ComboboxTrigger class="w-full" as-child>
        <Button variant="outline" class="justify-between">
          <template v-if="selectedOption?.label">
            <component v-if="isVNode(selectedOption.label)" :is="selectedOption.label" />
            <span v-else>{{ selectedOption.label }}</span>
          </template>
          <span v-else>Choose an option</span>

          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </ComboboxTrigger>
    </ComboboxAnchor>

    <ComboboxList class="w-full">
      <div class="relative w-full items-center">
        <ComboboxInput
          class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10"
          placeholder="Choose an option..."
        />
        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
          <Search class="size-4 text-muted-foreground" />
        </span>
      </div>

      <ComboboxEmpty> No option found. </ComboboxEmpty>

      <ComboboxGroup>
        <ComboboxItem
          v-for="option in options"
          :id="option.value"
          :key="option.value"
          :value="option.value"
          class="cursor-pointer pointer-events-none"
          @select="() => (selectedValue = option.value)"
        >
          <template v-if="isVNode(option.label)">
            <component :is="option.label" />
          </template>
          <span v-else>{{ option.label }}</span>

          <ComboboxItemIndicator>
            <Check :class="cn('ml-auto h-4 w-4')" />
          </ComboboxItemIndicator>
        </ComboboxItem>
      </ComboboxGroup>
    </ComboboxList>
  </Combobox>
</template>
