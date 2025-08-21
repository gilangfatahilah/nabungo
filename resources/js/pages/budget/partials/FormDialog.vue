<script setup lang="ts">
import { Budget } from "@/types";
import { watch, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { LoaderCircle, CalendarIcon } from "lucide-vue-next";

import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Dialog } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Popover, PopoverTrigger, PopoverContent } from "@/components/ui/popover";
import Combobox from "@/components/ComboBox.vue";
import MonthPicker from "@/components/MonthPicker.vue";
import FormContainerLayout from "@/components/common/dialog/FormContainerLayout.vue";
import InputIDR from "@/components/InputIDR.vue";

interface Props {
  header: {
    title: string;
    description?: string;
  };
  defaultValues?: Budget;
}

const props = defineProps<Props>();
const open = defineModel<boolean>("open");

const { defaultValues } = props;
const options = ref([]);
const loading = ref(false);

const firstDayThisMonth = new Date(
  Date.UTC(new Date().getUTCFullYear(), new Date().getUTCMonth(), 1)
);

const form = useForm({
  category_id: defaultValues?.category_id,
  month: defaultValues?.month ? new Date(defaultValues?.month) : firstDayThisMonth,
  amount: defaultValues?.amount,
});

const handleClose = (value: boolean) => {
  form.reset();
  open.value = value;
};

const formatDate = (d: Date) => {
  return d.toLocaleDateString("en-US", { month: "short", year: "numeric" });
};

const fetchOptions = async () => {
  loading.value = true;
  try {
    const response = await fetch(route("category.options", { type: "expense" }));
    const result = await response.json();

    const { data } = result;
    options.value = data;
  } catch (error) {
    console.log(`error : ${error}`);
  } finally {
    loading.value = false;
  }
};

const handleSubmit = () => {
  if (!defaultValues) {
    form.post(route("budget.store"), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Budget has successfully created.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  } else {
    form.put(route("budget.update", { id: defaultValues.id }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Budget has successfully updated.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  }
};

watch(open, (value) => {
  if (value) fetchOptions();
});

watch(
  () => props.defaultValues,
  (newValues) => {
    if (newValues) {
      const values = {
        category_id: newValues.category_id,
        month: new Date(newValues.month),
        amount: newValues.amount,
      };

      form.defaults(values);
    } else {
      form.reset();
    }
  },
  { immediate: true }
);
</script>

<template>
  <Dialog :open="open" @update:open="handleClose">
    <FormContainerLayout :header="header">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
        <div class="grid gap-6">
          <div class="grid gap-2">
            <Label for="type">Category</Label>
            <Combobox
              v-model:value="form.category_id"
              :loading="loading"
              :options="options"
              placeholder="Category"
            />
            <InputError :message="form.errors.category_id" />
          </div>

          <div class="grid gap-2">
            <Label for="type">Month</Label>
            <Popover>
              <PopoverTrigger as-child>
                <Button
                  variant="outline"
                  class="w-full justify-start text-left font-normal"
                  :class="{ 'text-muted-foreground': !form.month }"
                >
                  <CalendarIcon class="mr-2 h-4 w-4" />
                  <span v-if="form.month">{{ formatDate(form.month) }}</span>
                  <span v-else>Pick a month</span>
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0">
                <MonthPicker v-model="form.month" :variant="{ chevrons: 'ghost' }" />
              </PopoverContent>
            </Popover>
            <InputError :message="form.errors.month" />
          </div>

          <div class="grid gap-2">
            <Label for="amount">Amount</Label>
            <InputIDR v-model="form.amount" placeholder="Amount of Budget" />
            <InputError :message="form.errors.amount" />
          </div>

          <Button
            type="submit"
            class="mt-4 w-full"
            :tabindex="4"
            :disabled="form.processing"
          >
            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
            Save
          </Button>
        </div>
      </form>
    </FormContainerLayout>
  </Dialog>
</template>
