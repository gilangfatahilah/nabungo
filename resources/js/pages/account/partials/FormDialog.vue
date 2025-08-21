<script setup lang="ts">
import { Account } from "@/types";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { CreditCard, Landmark, LoaderCircle, Wallet } from "lucide-vue-next";
import { h, watch } from "vue";

import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Dialog } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import Combobox from "@/components/ComboBox.vue";
import FormContainerLayout from "@/components/common/dialog/FormContainerLayout.vue";
import IconLabel from "@/components/IconLabel.vue";

interface Props {
  header: {
    title: string;
    description?: string;
  };
  defaultValues?: Account;
}

const props = defineProps<Props>();
const open = defineModel<boolean>("open");

const { defaultValues } = props;
const options = [
  { label: h(IconLabel, { icon: Wallet, text: "Cash" }), value: "cash" },
  { label: h(IconLabel, { icon: Landmark, text: "Bank" }), value: "bank" },
  { label: h(IconLabel, { icon: CreditCard, text: "E-Wallet" }), value: "ewallet" },
  { label: h(IconLabel, { icon: Wallet, text: "Asset" }), value: "asset" },
  { label: h(IconLabel, { icon: Wallet, text: "Liability" }), value: "liability" },
];

const form = useForm({
  name: defaultValues?.name,
  type: defaultValues?.type,
  notes: defaultValues?.notes,
});

const handleClose = (value: boolean) => {
  form.reset();
  open.value = value;
};

const handleSubmit = () => {
  if (!defaultValues) {
    form.post(route("account.store"), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Account has successfully created.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  } else {
    form.put(route("account.update", { id: defaultValues.id }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Account has successfully updated.");
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
        name: newValues.name,
        type: newValues.type,
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
            <Label for="name">Name</Label>
            <Input
              id="name"
              type="text"
              required
              autofocus
              :tabindex="1"
              v-model="form.name"
              placeholder="Account Name"
            />
            <InputError :message="form.errors.name" />
          </div>

          <div class="grid gap-2">
            <Label for="type">Type</Label>
            <Combobox
              :options="options"
              v-model:value="form.type"
              placeholder="Account Type"
            />
            <InputError :message="form.errors.type" />
          </div>

          <div class="grid gap-2">
            <Label for="note">Note</Label>
            <Textarea
              v-model="form.notes"
              id="note"
              class="w-full"
              placeholder="Account Note"
            />
            <InputError :message="form.errors.name" />
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
