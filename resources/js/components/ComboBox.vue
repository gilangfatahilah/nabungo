<script setup lang="ts">
import { Check, ChevronsUpDown } from "lucide-vue-next";

import { computed, isVNode, VNode } from "vue";
import { Button } from "@/components/ui/button";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";

type Option = {
  label: string | VNode;
  value: string | number;
};

const { options, loading = false, disabled = false } = defineProps<{
  options: Option[];
  disabled?: boolean;
  loading?: boolean;
}>();

const open = defineModel<boolean>("open");
const selectedValue = defineModel<string | number | undefined>("value");

const selectedOption = computed(() => {
  return options.find((opt) => opt.value === selectedValue.value);
});
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        role="combobox"
        :aria-expanded="open"
        :disabled="loading || disabled"
        class="w-full justify-between"
      >
        <template v-if="selectedOption?.label">
          <component v-if="isVNode(selectedOption.label)" :is="selectedOption.label" />
          <span v-else>{{ selectedOption.label }}</span>
        </template>
        <span v-else>Choose an option</span>

        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-full p-0">
      <Command v-model="selectedValue">
        <CommandInput placeholder="Search framework..." />
        <CommandEmpty>No option found.</CommandEmpty>
        <CommandList>
          <CommandGroup>
            <CommandItem
              v-for="option in options"
              :key="option.value"
              :value="option.value"
              class="py-2"
              @select="open = false"
            >
              <Check
                :class="
                  cn('mr-2 h-4 w-4', value === option.value ? 'opacity-100' : 'opacity-0')
                "
              />
              <component v-if="isVNode(option.label)" :is="option.label" />
              <span v-else>{{ option.label }}</span>
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
