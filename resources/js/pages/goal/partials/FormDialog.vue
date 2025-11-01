<script setup lang="ts">
import { Goal } from "@/types";
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { LoaderCircle } from "lucide-vue-next";

import InputError from "@/components/InputError.vue";
import DatePicker from "@/components/DatePicker.vue";
import { Button } from "@/components/ui/button";
import { Dialog } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import FormContainerLayout from "@/components/common/dialog/FormContainerLayout.vue";
import InputIDR from "@/components/InputIDR.vue";

interface Props {
  header: {
    title: string;
    description?: string;
  };
  defaultValues?: Goal;
}

const props = defineProps<Props>();
const open = defineModel<boolean>("open");

const { defaultValues } = props;

const form = useForm({
  title: defaultValues?.title,
  target_amount: defaultValues?.target_amount,
  due_date: defaultValues?.due_date ? new Date(defaultValues.due_date) : new Date(),
  notes: defaultValues?.notes,
});

const handleClose = (value: boolean) => {
  form.reset();
  open.value = value;
};

const handleSubmit = () => {
  if (!defaultValues) {
    form.post(route("goal.store"), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Goal has successfully created.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  } else {
    form.put(route("goal.update", { id: defaultValues.id }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Goal has successfully updated.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  }
};

watch(
  () => props.defaultValues,
  (newValues) => {
    if (newValues) {
      const values = {
        title: newValues.title,
        target_amount: newValues.target_amount,
        due_date: newValues.due_date ? new Date(newValues.due_date) : new Date(),
        notes: newValues.notes,
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
            <Label for="type">Title</Label>
            <Input v-model="form.title" id="title" placeholder="Title" />
            <InputError :message="form.errors.title" />
          </div>

          <div class="grid gap-2">
            <Label for="amount">Target Amount</Label>
            <InputIDR v-model="form.target_amount" placeholder="Amount of Transaction" />
            <InputError :message="form.errors.target_amount" />
          </div>

          <div class="grid gap-2">
            <Label>Due Date</Label>
            <DatePicker v-model="form.due_date" />
            <InputError :message="form.errors.due_date" />
          </div>

          <div class="grid gap-2">
            <Label for="note">Note</Label>
            <Textarea
              v-model="form.notes"
              id="note"
              class="w-full"
              placeholder="Goal Note"
            />
            <InputError :message="form.errors.notes" />
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
