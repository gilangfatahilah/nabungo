<script setup lang="ts">
import { ref, watch } from "vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Card, CardContent } from "@/components/ui/card";
import { X } from "lucide-vue-next";
import DatePicker from "@/components/DatePicker.vue";
import MultiSelect from "@/components/MultiSelect.vue";
import InputIDR from "@/components/InputIDR.vue";

export interface FieldOption {
  key: string;
  label: string;
  type: "string" | "number" | "date" | "enum";
  operators: string[];
  enumOptions?: { label: string; value: string }[];
}

export interface FilterRow {
  field: string;
  operator: string;
  value: any;
}

const props = defineProps<{
  fields: FieldOption[];
  modelValue: FilterRow[];
  reset?: boolean;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", value: FilterRow[]): void;
  (e: "apply"): void;
}>();

const filters = ref<FilterRow[]>(props.modelValue);

const addFilter = () => {
  if (props.fields.length === 0) return;
  filters.value.push({
    field: props.fields[0].key,
    operator: props.fields[0].operators[0],
    value: "",
  });
  emit("update:modelValue", filters.value);
};

const removeFilter = (index: number) => {
  filters.value.splice(index, 1);
  emit("update:modelValue", filters.value);
};

const clearFilters = () => {
  filters.value = [];
  emit("update:modelValue", filters.value);
};

const updateField = (index: number, fieldKey: string) => {
  const field = props.fields.find((f) => f.key === fieldKey);
  if (!field) return;
  filters.value[index].field = field.key;
  filters.value[index].operator = field.operators[0];
  filters.value[index].value = "";
  emit("update:modelValue", filters.value);
};

watch(filters.value, (newFilters) => {
  console.table(newFilters);
});

watch(
  () => props.reset,
  (newVal) => {
    if (newVal) {
      filters.value = [];
      emit("update:modelValue", filters.value);
    }
  },
  { immediate: true }
);
</script>

<template>
  <Card class="p-4 space-y-2">
    <CardContent class="space-y-2">
      <div
        v-for="(filter, index) in filters"
        :key="index"
        class="flex items-center gap-2"
      >
        <!-- Field Select -->
        <Select
          :model-value="filter.field"
          @update:model-value="(val) => updateField(index, val)"
        >
          <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Field" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="f in fields" :key="f.key" :value="f.key">
              {{ f.label }}
            </SelectItem>
          </SelectContent>
        </Select>

        <!-- Operator Select -->
        <Select
          :model-value="filter.operator"
          @update:model-value="
            (val) => {
              filter.operator = val;
              emit('update:modelValue', filters);
            }
          "
        >
          <SelectTrigger class="w-[120px]">
            <SelectValue placeholder="Operator" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="op in fields.find((f) => f.key === filter.field)?.operators || []"
              :key="op"
              :value="op"
            >
              {{ op }}
            </SelectItem>
          </SelectContent>
        </Select>

        <!-- Value Input -->
        <div class="flex-1">
          <Input
            v-if="fields.find((f) => f.key === filter.field)?.type === 'string'"
            placeholder="Enter text"
            v-model="filter.value"
            @input="emit('update:modelValue', filters)"
          />

          <Input
            v-else-if="
              fields.find((f) => f.key === filter.field)?.type === 'number' &&
              fields.find((f) => f.key === filter.field)?.label !== 'Amount'
            "
            type="number"
            placeholder="Enter number"
            v-model="filter.value"
            @input="emit('update:modelValue', filters)"
          />

          <InputIDR
            v-else-if="
              fields.find((f) => f.key === filter.field)?.label === 'Amount' &&
              fields.find((f) => f.key === filter.field)?.type === 'number'
            "
            v-model="filter.value"
          />

          <DatePicker
            v-else-if="fields.find((f) => f.key === filter.field)?.type === 'date'"
            v-model="filter.value"
            @input="emit('update:modelValue', filters)"
          />

          <MultiSelect
            v-if="
              fields.find((f) => f.key === filter.field)?.type === 'enum' &&
              (filter.operator === 'in' || filter.operator === 'not in')
            "
            v-model="filter.value"
            :options="fields.find((f) => f.key === filter.field)?.enumOptions || []"
            placeholder="Select options"
          />

          <Select
            v-if="
              fields.find((f) => f.key === filter.field)?.type === 'enum' &&
              filter.operator !== 'in' &&
              filter.operator !== 'not in'
            "
            v-model="filter.value"
          >
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select option" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="option in fields.find((f) => f.key === filter.field)
                    ?.enumOptions || []"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>

        <!-- Remove Button -->
        <Button variant="ghost" size="icon" @click="removeFilter(index)">
          <X class="w-4 h-4" />
        </Button>
      </div>
    </CardContent>

    <div class="px-6 flex justify-between items-center">
      <Button variant="secondary" size="sm" @click="addFilter">+ Add filter</Button>

      <div class="flex items-center gap-2">
        <Button size="sm" @click="emit('apply')">Apply</Button>

        <Button variant="ghost" size="sm" @click="clearFilters">Clear all</Button>
      </div>
    </div>
  </Card>
</template>
