<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  SelectGroup,
} from "@/components/ui/select";
import { Card, CardContent } from "@/components/ui/card";
import { X, Plus, Filter, RotateCcw } from "lucide-vue-next";
import DatePicker from "@/components/DatePicker.vue";
import MultiSelect from "@/components/MultiSelect.vue";
import InputIDR from "@/components/InputIDR.vue";
import { Checkbox } from "@/components/ui/checkbox";
import DateRangePicker from "@/components/DateRangePicker.vue";

// Enhanced interfaces to match PHP QueryFilters
export interface FieldOption {
  key: string;
  label: string;
  type:
    | "string"
    | "number"
    | "float"
    | "date"
    | "datetime"
    | "boolean"
    | "enum"
    | "array";
  operators: string[];
  enumOptions?: { label: string; value: string | number | boolean }[];
  validation?: {
    min?: number;
    max?: number;
    required?: boolean;
    format?: string;
  };
}

export interface FilterRow {
  field: string;
  operator: string;
  value: any;
  id?: string; // Add unique ID for better tracking
}

const props = defineProps<{
  fields: FieldOption[];
  modelValue: FilterRow[];
  reset?: boolean;
  maxFilters?: number;
  showValidationErrors?: boolean;
}>();

console.log(props.modelValue);

const emit = defineEmits<{
  (e: "update:modelValue", value: FilterRow[]): void;
  (e: "apply", filters: FilterRow[]): void;
  (e: "clear"): void;
  (e: "validation-error", errors: string[]): void;
}>();

const filters = ref<FilterRow[]>(
  props.modelValue.map((filter) => ({
    ...filter,
    id: filter.id || generateId(),
  }))
);

const validationErrors = ref<Record<string, string[]>>({});

// Computed properties
const hasFilters = computed(() => filters.value.length > 0);
const canAddMoreFilters = computed(
  () => !props.maxFilters || filters.value.length < props.maxFilters
);

const validFilters = computed(() => {
  return filters.value.filter((filter) => {
    const field = getFieldConfig(filter.field);
    return field && filter.operator && isValidValue(filter, field);
  });
});

// Helper functions
function generateId(): string {
  return Math.random().toString(36).substr(2, 9);
}

function getFieldConfig(fieldKey: string): FieldOption | undefined {
  return props.fields.find((f) => f.key === fieldKey);
}

function isNullOperator(operator: string): boolean {
  return ["is null", "is not null"].includes(operator);
}

function requiresArrayValue(operator: string): boolean {
  return ["between", "not between", "in", "not in"].includes(operator);
}

function isValidValue(filter: FilterRow, field: FieldOption): boolean {
  if (isNullOperator(filter.operator)) {
    return true;
  }

  if (requiresArrayValue(filter.operator)) {
    return Array.isArray(filter.value) && filter.value.length > 0;
  }

  if (filter.operator === "between" || filter.operator === "not between") {
    return Array.isArray(filter.value) && filter.value.length === 2;
  }

  return filter.value !== null && filter.value !== undefined && filter.value !== "";
}

function validateFilter(filter: FilterRow): string[] {
  const errors: string[] = [];
  const field = getFieldConfig(filter.field);

  if (!field) {
    errors.push(`Invalid field: ${filter.field}`);
    return errors;
  }

  if (!field.operators.includes(filter.operator)) {
    errors.push(`Invalid operator "${filter.operator}" for field "${field.label}"`);
  }

  if (!isNullOperator(filter.operator)) {
    if (filter.operator === "between" || filter.operator === "not between") {
      if (!Array.isArray(filter.value) || filter.value.length !== 2) {
        errors.push(`"${filter.operator}" requires exactly 2 values`);
      }
    } else if (requiresArrayValue(filter.operator)) {
      if (!Array.isArray(filter.value) || filter.value.length === 0) {
        errors.push(`"${filter.operator}" requires at least one value`);
      }
    } else if (!filter.value && filter.value !== 0 && filter.value !== false) {
      errors.push(`Value is required for "${field.label}"`);
    }
  }

  return errors;
}

function getDefaultValue(field: FieldOption, operator: string): any {
  if (isNullOperator(operator)) {
    return null;
  }

  if (requiresArrayValue(operator)) {
    return [];
  }

  switch (field.type) {
    case "boolean":
      return false;
    case "number":
    case "float":
      return 0;
    case "date":
    case "datetime":
      return "";
    case "array":
      return [];
    default:
      return "";
  }
}

// Main functions
const addFilter = () => {
  if (!canAddMoreFilters.value || props.fields.length === 0) return;

  const firstField = props.fields[0];
  const defaultOperator = firstField.operators[0];

  const newFilter: FilterRow = {
    id: generateId(),
    field: firstField.key,
    operator: defaultOperator,
    value: getDefaultValue(firstField, defaultOperator),
  };

  filters.value.push(newFilter);
  emitUpdate();
};

const removeFilter = (index: number) => {
  const filterId = filters.value[index]?.id;
  if (filterId && validationErrors.value[filterId]) {
    delete validationErrors.value[filterId];
  }
  filters.value.splice(index, 1);
  emitUpdate();
};

const clearFilters = () => {
  filters.value = [];
  validationErrors.value = {};
  emitUpdate();
  emit("clear");
};

const updateField = (index: number, fieldKey: string) => {
  const field = getFieldConfig(fieldKey);
  if (!field) return;

  const filter = filters.value[index];
  const oldOperator = filter.operator;
  const newOperator = field.operators.includes(oldOperator)
    ? oldOperator
    : field.operators[0];

  filter.field = field.key;
  filter.operator = newOperator;
  filter.value = getDefaultValue(field, newOperator);

  // Clear validation errors for this filter
  if (filter.id && validationErrors.value[filter.id]) {
    delete validationErrors.value[filter.id];
  }

  emitUpdate();
};

const updateOperator = (index: number, operator: string) => {
  const filter = filters.value[index];
  const field = getFieldConfig(filter.field);
  if (!field) return;

  filter.operator = operator;
  filter.value = getDefaultValue(field, operator);

  // Clear validation errors for this filter
  if (filter.id && validationErrors.value[filter.id]) {
    delete validationErrors.value[filter.id];
  }

  emitUpdate();
};

const updateValue = (index: number, value: any) => {
  const filter = filters.value[index];
  filter.value = value;

  // Clear validation errors for this filter
  if (filter.id && validationErrors.value[filter.id]) {
    delete validationErrors.value[filter.id];
  }

  emitUpdate();
};

const emitUpdate = () => {
  emit("update:modelValue", filters.value);
};

const applyFilters = () => {
  // Validate all filters
  const allErrors: string[] = [];
  const newValidationErrors: Record<string, string[]> = {};

  filters.value.forEach((filter) => {
    if (filter.id) {
      const errors = validateFilter(filter);
      if (errors.length > 0) {
        newValidationErrors[filter.id] = errors;
        allErrors.push(...errors);
      }
    }
  });

  validationErrors.value = newValidationErrors;

  if (allErrors.length > 0 && props.showValidationErrors) {
    emit("validation-error", allErrors);
    return;
  }

  emit("apply", validFilters.value);
};

// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    filters.value = newValue.map((filter) => ({
      ...filter,
      id: filter.id || generateId(),
    }));
  },
  { deep: true }
);

watch(
  () => props.reset,
  (newVal) => {
    if (newVal) {
      clearFilters();
    }
  },
  { immediate: true }
);
</script>

<template>
  <Card class="p-4 space-y-4">
    <CardContent class="space-y-3">
      <!-- Filter Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Filter class="w-4 h-4 text-muted-foreground" />
          <span class="text-sm font-medium">Filters</span>
          <span v-if="hasFilters" class="text-xs text-muted-foreground">
            ({{ filters.length }})
          </span>
        </div>
        <Button
          v-if="hasFilters"
          variant="ghost"
          size="sm"
          @click="clearFilters"
          class="h-7"
        >
          <RotateCcw class="w-3 h-3 mr-1" />
          Clear all
        </Button>
      </div>

      <!-- Filter Rows -->
      <div class="space-y-2">
        <div
          v-for="(filter, index) in filters"
          :key="filter.id"
          class="flex items-start gap-2 p-2 rounded-lg border bg-muted/30"
        >
          <!-- Field Select -->
          <div class="min-w-[180px]">
            <Select
              :model-value="filter.field"
              @update:model-value="(val) => updateField(index, val)"
            >
              <SelectTrigger class="h-9 w-full">
                <SelectValue placeholder="Select field" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="f in fields" :key="f.key" :value="f.key">
                  {{ f.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Operator Select -->
          <div class="min-w-[140px]">
            <Select
              :model-value="filter.operator"
              @update:model-value="(val) => updateOperator(index, val)"
            >
              <SelectTrigger class="h-9 w-full">
                <SelectValue placeholder="Operator" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="op in getFieldConfig(filter.field)?.operators || []"
                  :key="op"
                  :value="op"
                >
                  {{ op }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Value Input -->
          <div class="flex-1 min-w-0">
            <!-- Null operators don't need value input -->
            <div
              v-if="isNullOperator(filter.operator)"
              class="h-9 flex items-center px-3 text-sm text-muted-foreground border rounded-md bg-muted"
            >
              No value required
            </div>

            <!-- String input -->
            <Input
              v-else-if="
                getFieldConfig(filter.field)?.type === 'string' &&
                !requiresArrayValue(filter.operator)
              "
              :placeholder="
                filter.operator === 'like' || filter.operator === 'not like'
                  ? 'Search text...'
                  : 'Enter text'
              "
              :model-value="filter.value"
              @update:model-value="(val) => updateValue(index, val)"
              class="h-9"
            />

            <!-- Number input (non-currency) -->
            <Input
              v-else-if="
                (getFieldConfig(filter.field)?.type === 'number' ||
                  getFieldConfig(filter.field)?.type === 'float') &&
                getFieldConfig(filter.field)?.label !== 'Amount' &&
                !requiresArrayValue(filter.operator)
              "
              type="number"
              placeholder="Enter number"
              :model-value="filter.value"
              @update:model-value="(val) => updateValue(index, val)"
              class="h-9"
            />

            <!-- Currency input -->
            <InputIDR
              v-else-if="
                getFieldConfig(filter.field)?.label === 'Amount' &&
                (getFieldConfig(filter.field)?.type === 'number' ||
                  getFieldConfig(filter.field)?.type === 'float') &&
                !requiresArrayValue(filter.operator)
              "
              :model-value="filter.value"
              @update:model-value="(val) => updateValue(index, val)"
            />

            <!-- Date input -->
            <DatePicker
              v-else-if="
                (getFieldConfig(filter.field)?.type === 'date' ||
                  getFieldConfig(filter.field)?.type === 'datetime') &&
                !requiresArrayValue(filter.operator)
              "
              :model-value="filter.value"
              @update:model-value="(val) => updateValue(index, val)"
            />

            <!-- Date Range Picker -->
            <DateRangePicker
              v-else-if="
                getFieldConfig(filter.field)?.type === 'date' &&
                filter.operator.includes('between')
              "
              :model-value="{
                start: filter.value[0],
                end: filter.value[1],
              }"
              @update:model-value="(val) => updateValue(index, [val.start, val.end])"
            />

            <!-- Boolean input -->
            <div
              v-else-if="getFieldConfig(filter.field)?.type === 'boolean'"
              class="flex items-center space-x-2 h-9"
            >
              <Checkbox
                :checked="filter.value"
                @update:checked="(val) => updateValue(index, val)"
                :id="`checkbox-${filter.id}`"
              />
              <label :for="`checkbox-${filter.id}`" class="text-sm">
                {{ filter.value ? "True" : "False" }}
              </label>
            </div>

            <!-- Multi-select for array operators -->
            <MultiSelect
              v-else-if="
                requiresArrayValue(filter.operator) &&
                !filter.operator.includes('between')
              "
              :model-value="filter.value || []"
              @update:model-value="(val) => updateValue(index, val)"
              :options="getFieldConfig(filter.field)?.enumOptions || []"
              placeholder="Select options"
            />

            <!-- Single enum select -->
            <Select
              v-else-if="getFieldConfig(filter.field)?.type === 'enum'"
              :model-value="filter.value"
              @update:model-value="(val) => updateValue(index, val)"
            >
              <SelectTrigger class="h-9 w-full">
                <SelectValue placeholder="Select option" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem
                    v-for="option in getFieldConfig(filter.field)?.enumOptions || []"
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
          <Button
            variant="ghost"
            size="icon"
            @click="removeFilter(index)"
            class="h-9 w-9 shrink-0"
          >
            <X class="w-4 h-4" />
          </Button>
        </div>

        <!-- Validation Errors -->
        <div
          v-if="props.showValidationErrors && Object.keys(validationErrors).length > 0"
          class="space-y-1"
        >
          <div
            v-for="(errors, filterId) in validationErrors"
            :key="filterId"
            class="text-sm text-destructive space-y-1"
          >
            <div v-for="error in errors" :key="error" class="flex items-center gap-1">
              <span class="w-1 h-1 bg-destructive rounded-full"></span>
              {{ error }}
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!hasFilters" class="text-center text-muted-foreground">
        <Filter class="w-8 h-8 mx-auto mb-2 opacity-50" />
        <p class="text-sm">No filters applied</p>
        <p class="text-xs">Add filters to refine your search</p>
      </div>
    </CardContent>

    <!-- Actions -->
    <div class="px-6 flex justify-between items-center border-t pt-4">
      <Button
        variant="outline"
        size="sm"
        @click="addFilter"
        :disabled="!canAddMoreFilters"
      >
        <Plus class="w-4 h-4 mr-1" />
        Add filter
        <span v-if="props.maxFilters" class="ml-1 text-xs text-muted-foreground">
          ({{ filters.length }}/{{ props.maxFilters }})
        </span>
      </Button>

      <div class="flex items-center gap-2">
        <Button size="sm" @click="applyFilters" :disabled="!hasFilters">
          <Filter class="w-4 h-4 mr-1" />
          Apply Filters
          <span v-if="validFilters.length !== filters.length" class="ml-1 text-xs">
            ({{ validFilters.length }})
          </span>
        </Button>
      </div>
    </div>
  </Card>
</template>
