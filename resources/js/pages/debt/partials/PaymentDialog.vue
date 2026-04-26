<script setup lang="ts">
import { Debt } from "@/types";
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { LoaderCircle } from "lucide-vue-next";

import InputError from "@/components/InputError.vue";
import DatePicker from "@/components/DatePicker.vue";
import { Button } from "@/components/ui/button";
import { Dialog } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import FormContainerLayout from "@/components/common/dialog/FormContainerLayout.vue";
import InputIDR from "@/components/InputIDR.vue";
import Combobox from "@/components/ComboBox.vue";

interface Option {
  label: string;
  value: number;
}

interface Props {
  debt: Debt;
}

const props = defineProps<Props>();
const open = defineModel<boolean>("open");

const accountOptions = ref<Option[]>([]);
const accountComboOpen = ref(false);
const loadingAccounts = ref(false);

const fetchAccounts = async () => {
  loadingAccounts.value = true;
  try {
    const res = await fetch(route("account.options"));
    const { data } = await res.json();
    accountOptions.value = data;
  } catch {
    accountOptions.value = [];
  } finally {
    loadingAccounts.value = false;
  }
};

const form = useForm({
  amount:       undefined as number | undefined,
  payment_date: new Date() as Date,
  account_id:   undefined as number | undefined,
  notes:        "",
});

const handleClose = (value: boolean) => {
  form.reset();
  open.value = value;
};

const handleSubmit = () => {
  form.post(route("debt.payment.store", { debt: props.debt.id }), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Pembayaran berhasil dicatat.");
      handleClose(false);
    },
    onError: (errors) => {
      const msg = errors.error ?? "Gagal mencatat pembayaran.";
      toast.error(msg);
    },
  });
};

watch(open, (v) => {
  if (v) fetchAccounts();
});
</script>

<template>
  <Dialog :open="open" @update:open="handleClose">
    <FormContainerLayout
      :header="{
        title: 'Record Payment',
        description: `Record a payment for: ${debt.title}`,
      }"
    >
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
        <div class="grid gap-6">
          <!-- Amount -->
          <div class="grid gap-2">
            <Label>Payment Amount</Label>
            <InputIDR v-model="form.amount" :placeholder="`Max: ${Number(debt.remaining_amount).toLocaleString('id-ID')}`" />
            <InputError :message="form.errors.amount" />
          </div>

          <!-- Payment Date -->
          <div class="grid gap-2">
            <Label>Payment Date</Label>
            <DatePicker v-model="form.payment_date" />
            <InputError :message="form.errors.payment_date" />
          </div>

          <!-- Account (optional) -->
          <div class="grid gap-2">
            <Label>Deduct from Account <span class="text-muted-foreground text-xs">(optional)</span></Label>
            <Combobox
              v-model:open="accountComboOpen"
              v-model:value="form.account_id"
              :options="accountOptions"
              :loading="loadingAccounts"
            />
            <p class="text-xs text-muted-foreground">
              If selected, an expense transaction will automatically be created.
            </p>
            <InputError :message="form.errors.account_id" />
          </div>

          <!-- Notes -->
          <div class="grid gap-2">
            <Label for="notes">Notes</Label>
            <Textarea v-model="form.notes" id="notes" placeholder="Payment notes..." />
            <InputError :message="form.errors.notes" />
          </div>
        </div>

        <Button type="submit" :disabled="form.processing" class="w-full">
          <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
          Record Payment
        </Button>
      </form>
    </FormContainerLayout>
  </Dialog>
</template>
