<script setup lang="ts">
import "vue3-emoji-picker/css";

import { Category } from "@/types";
import { watch, ref, h } from "vue";
import { useAppearance } from "@/composables/useAppearance";
import EmojiPicker, { EmojiExt } from "vue3-emoji-picker";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { ArrowDownCircle, ArrowUpCircle, LoaderCircle } from "lucide-vue-next";

import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Dialog } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import Combobox from "@/components/ComboBox.vue";
import IconLabel from "@/components/IconLabel.vue";
import FormContainerLayout from "@/components/common/dialog/FormContainerLayout.vue";

interface Props {
  header: {
    title: string;
    description?: string;
  };
  defaultValues?: Category;
}

const props = defineProps<Props>();
const open = defineModel<boolean>("open");

const { defaultValues } = props;
const options = [
  {
    label: h(IconLabel, { icon: ArrowDownCircle, text: "Income" }),
    value: "income",
  },
  {
    label: h(IconLabel, { icon: ArrowUpCircle, text: "Expense" }),
    value: "expense",
  },
];

const showEmojiPicker = ref(false);
const { appearance } = useAppearance();
const form = useForm({
  name: defaultValues?.name,
  type: defaultValues?.type,
});

const handleClose = (value: boolean) => {
  form.reset();
  open.value = value;
};

function handleEmojiSelect(emoji: EmojiExt) {
  form.name = (form.name ?? "") + emoji.i;
  showEmojiPicker.value = false;
}

const handleSubmit = () => {
  if (!defaultValues) {
    form.post(route("category.store"), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Category has successfully created.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  } else {
    form.put(route("category.update", { id: defaultValues.id }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Category has successfully updated.");
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
            <div class="flex gap-2 relative">
              <Input
                id="name"
                type="text"
                required
                autofocus
                :tabindex="1"
                v-model="form.name"
                placeholder="Category Name"
                class="flex-1"
              />
              <Button
                type="button"
                variant="outline"
                @click="showEmojiPicker = !showEmojiPicker"
                :tabindex="2"
              >
                😊
              </Button>
            </div>
            <InputError :message="form.errors.name" />

            <div v-if="showEmojiPicker" class="absolute top-10 z-[60]">
              <EmojiPicker
                :theme="appearance === 'system' ? 'light' : appearance"
                @select="handleEmojiSelect"
              />
            </div>
          </div>

          <div class="grid gap-2">
            <Label for="type">Type</Label>
            <Combobox
              :options="options"
              v-model:value="form.type"
              placeholder="Category Type"
            />
            <InputError :message="form.errors.type" />
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
