<script setup lang="ts">
import { Debt } from "@/types";
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import FormContainerLayout from "@/components/common/dialog/FormContainerLayout.vue";
import InputIDR from "@/components/InputIDR.vue";

interface Props {
  header: {
    title: string;
    description?: string;
  };
  defaultValues?: Debt;
}

const props = defineProps<Props>();
const open = defineModel<boolean>("open");

const form = useForm({
  title:         props.defaultValues?.title         ?? "",
  type:          props.defaultValues?.type          ?? "debt",
  amount:        props.defaultValues?.amount,
  contact_name:  props.defaultValues?.contact_name  ?? "",
  contact_phone: props.defaultValues?.contact_phone ?? "",
  due_date:      props.defaultValues?.due_date ? new Date(props.defaultValues.due_date) : null as Date | null,
  notes:         props.defaultValues?.notes         ?? "",
});

const handleClose = (value: boolean) => {
  form.reset();
  open.value = value;
};

const handleSubmit = () => {
  if (!props.defaultValues) {
    form.post(route("debt.store"), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Hutang/piutang berhasil ditambahkan.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Gagal menyimpan. Periksa kembali data yang diisi.");
      },
    });
  } else {
    form.put(route("debt.update", { debt: props.defaultValues.id }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Hutang/piutang berhasil diperbarui.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Gagal memperbarui. Periksa kembali data yang diisi.");
      },
    });
  }
};

watch(
  () => props.defaultValues,
  (v) => {
    if (v) {
      form.defaults({
        title:         v.title,
        type:          v.type,
        amount:        v.amount,
        contact_name:  v.contact_name  ?? "",
        contact_phone: v.contact_phone ?? "",
        due_date:      v.due_date ? new Date(v.due_date) : null,
        notes:         v.notes ?? "",
      });
      form.reset();
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
          <!-- Type -->
          <div class="grid gap-2">
            <Label>Type</Label>
            <Select v-model="form.type">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Select type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="debt">Debt (I owe someone)</SelectItem>
                <SelectItem value="receivable">Receivable (Someone owes me)</SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.type" />
          </div>

          <!-- Title -->
          <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input v-model="form.title" id="title" placeholder="e.g. Loan from John" />
            <InputError :message="form.errors.title" />
          </div>

          <!-- Amount -->
          <div class="grid gap-2">
            <Label>Total Amount</Label>
            <InputIDR v-model="form.amount" placeholder="Total amount" />
            <InputError :message="form.errors.amount" />
          </div>

          <!-- Contact -->
          <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
              <Label for="contact_name">Contact Name</Label>
              <Input v-model="form.contact_name" id="contact_name" placeholder="Name" />
              <InputError :message="form.errors.contact_name" />
            </div>
            <div class="grid gap-2">
              <Label for="contact_phone">Contact Phone</Label>
              <Input v-model="form.contact_phone" id="contact_phone" placeholder="Phone" />
              <InputError :message="form.errors.contact_phone" />
            </div>
          </div>

          <!-- Due Date -->
          <div class="grid gap-2">
            <Label>Due Date</Label>
            <DatePicker v-model="form.due_date" />
            <InputError :message="form.errors.due_date" />
          </div>

          <!-- Notes -->
          <div class="grid gap-2">
            <Label for="notes">Notes</Label>
            <Textarea v-model="form.notes" id="notes" placeholder="Additional notes..." />
            <InputError :message="form.errors.notes" />
          </div>
        </div>

        <Button type="submit" :disabled="form.processing" class="w-full">
          <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
          {{ defaultValues ? "Update" : "Save" }}
        </Button>
      </form>
    </FormContainerLayout>
  </Dialog>
</template>
