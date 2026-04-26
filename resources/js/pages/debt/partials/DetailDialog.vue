<script setup lang="ts">
import { Debt, DebtPayment } from "@/types";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Trash2 } from "lucide-vue-next";

import { Dialog, DialogContent } from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { Separator } from "@/components/ui/separator";
import { Button } from "@/components/ui/button";
import { formatIdr } from "@/lib/utils";
import { getStatusVariant, getTypeVariant } from "./column";

interface Props {
  data: Debt;
}

const { data } = defineProps<Props>();
const open = defineModel<boolean>("open");

const payments = ref<DebtPayment[]>([]);
const loading = ref(false);

const fetchPayments = async () => {
  // Payments are eager-loaded via the index page prop; if not available, we skip.
  if (data.payments) {
    payments.value = data.payments;
  }
};

const handleDeletePayment = (payment: DebtPayment) => {
  loading.value = true;
  router.delete(route("debt.payment.destroy", payment.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Pembayaran berhasil dihapus.");
      payments.value = payments.value.filter((p) => p.id !== payment.id);
      loading.value = false;
    },
    onError: () => {
      toast.error("Gagal menghapus pembayaran.");
      loading.value = false;
    },
  });
};

watch(() => data, fetchPayments, { immediate: true });
</script>

<template>
  <Dialog :open="open" @update:open="open = $event">
    <DialogContent class="gap-2 max-w-md">
      <h1 class="my-2 font-semibold text-muted-foreground">{{ data.title }}</h1>

      <div class="flex flex-col gap-4">
        <!-- Progress -->
        <div class="space-y-2">
          <p class="text-muted-foreground font-semibold">
            <span
              :class="[
                'md:text-3xl font-bold',
                data.progress < 100 ? 'text-primary' : 'text-green-500',
              ]"
            >{{ data.progress }}%</span>
            paid
          </p>
          <Progress :model-value="data.progress" />
        </div>

        <Separator />

        <!-- Badges -->
        <div class="flex gap-2 flex-wrap">
          <Badge :variant="getTypeVariant(data.type)" class="capitalize">
            {{ data.type === 'debt' ? 'Debt' : 'Receivable' }}
          </Badge>
          <Badge :variant="getStatusVariant(data.status)" class="capitalize">
            {{ data.status }}
          </Badge>
        </div>

        <!-- Amounts -->
        <div class="grid grid-cols-3 gap-3">
          <div class="space-y-1">
            <p class="text-muted-foreground text-xs">Total</p>
            <p class="font-medium text-sm">{{ formatIdr(Number(data.amount), true) }}</p>
          </div>
          <div class="space-y-1">
            <p class="text-muted-foreground text-xs">Paid</p>
            <p class="font-medium text-sm text-green-600">{{ formatIdr(Number(data.paid_amount), true) }}</p>
          </div>
          <div class="space-y-1">
            <p class="text-muted-foreground text-xs">Remaining</p>
            <p class="font-medium text-sm text-destructive">{{ formatIdr(Number(data.remaining_amount), true) }}</p>
          </div>
        </div>

        <!-- Contact & Due Date -->
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1" v-if="data.contact_name">
            <p class="text-muted-foreground text-xs">Contact</p>
            <p class="text-sm">{{ data.contact_name }}</p>
            <p v-if="data.contact_phone" class="text-xs text-muted-foreground">{{ data.contact_phone }}</p>
          </div>
          <div class="space-y-1">
            <p class="text-muted-foreground text-xs">Due Date</p>
            <p class="text-sm">{{ data.formatted_due_date }}</p>
          </div>
        </div>

        <div v-if="data.notes" class="space-y-1">
          <p class="text-muted-foreground text-xs">Notes</p>
          <p class="text-sm">{{ data.notes }}</p>
        </div>

        <!-- Payment History -->
        <Separator />

        <div>
          <p class="text-muted-foreground text-sm font-semibold mb-2">Payment History</p>
          <div v-if="payments.length === 0" class="text-sm text-muted-foreground">
            No payments recorded yet.
          </div>
          <div v-else class="flex flex-col gap-2 max-h-48 overflow-y-auto">
            <div
              v-for="payment in payments"
              :key="payment.id"
              class="flex items-center justify-between rounded-md border px-3 py-2 text-sm"
            >
              <div>
                <p class="font-medium">{{ formatIdr(Number(payment.amount), true) }}</p>
                <p class="text-xs text-muted-foreground">{{ payment.payment_date }}</p>
                <p v-if="payment.notes" class="text-xs text-muted-foreground">{{ payment.notes }}</p>
              </div>
              <Button
                variant="ghost"
                size="icon"
                class="text-destructive h-7 w-7"
                :disabled="loading"
                @click="handleDeletePayment(payment)"
              >
                <Trash2 :size="14" />
              </Button>
            </div>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
