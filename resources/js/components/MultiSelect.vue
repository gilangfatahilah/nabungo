<script setup lang="ts">
import { ref, watch, computed } from "vue";
import {
  Command,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandEmpty,
} from "@/components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { X, ChevronsUpDown } from "lucide-vue-next";
import { Checkbox } from "./ui/checkbox";

export interface Option {
  label: string;
  value: string | number;
}

interface Props {
  modelValue: (string | number)[]; // v-model binding
  options: Option[];
  placeholder?: string;
  defaultValue?: (string | number)[];
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: "Select options...",
  defaultValue: () => [],
});

const emit = defineEmits<{
  (e: "update:modelValue", value: (string | number)[]): void;
}>();

// --- state
const selected = ref<(string | number)[]>([...props.modelValue, ...props.defaultValue]);

// --- computed untuk akses cepat
const selectedLabels = computed(() =>
  selected.value.map((val) => {
    return props.options.find((opt) => opt.value === val)?.label ?? String(val);
  })
);

// --- sync ke parent
watch(selected, (val) => {
  emit("update:modelValue", val);
});

// --- helpers
const toggleValue = (val: string | number) => {
  selected.value = selected.value.includes(val)
    ? selected.value.filter((v) => v !== val)
    : [...selected.value, val];
};

const removeValue = (val: string | number) => {
  selected.value = selected.value.filter((v) => v !== val);
};

const isSelected = (val: string | number) => selected.value.includes(val);
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" class="w-full justify-between">
        <div class="flex flex-wrap gap-1">
          <template v-if="selected.length">
            <Badge
              v-for="(label, i) in selectedLabels"
              :key="selected[i]"
              variant="secondary"
              class="flex items-center gap-1 z-20"
            >
              {{ label }}
              <button
                type="button"
                class="flex items-center"
                @click.stop.prevent="removeValue(selected[i])"
              >
                <X class="w-3 h-3" />
              </button>
            </Badge>
          </template>
          <span v-else class="text-muted-foreground">
            {{ placeholder }}
          </span>
        </div>
        <ChevronsUpDown class="w-4 h-4 opacity-50 ml-2 shrink-0" />
      </Button>
    </PopoverTrigger>

    <PopoverContent class="p-0 w-[250px]">
      <Command>
        <CommandInput placeholder="Search..." />
        <CommandList>
          <CommandEmpty>No option found.</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="opt in options"
              :key="opt.value"
              :value="opt.label"
              @select="toggleValue(opt.value)"
            >
              <div class="flex items-center justify-between w-full">
                <span>{{ opt.label }}</span>
                <Checkbox
                  :model-value="isSelected(opt.value)"
                  class="ml-2 pointer-events-none"
                  readonly
                />
              </div>
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
