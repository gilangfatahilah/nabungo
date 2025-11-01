<script setup lang="ts">
import { Budget, Transaction } from "@/types";
import { watch, ref, Ref, reactive } from "vue";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { CheckCircle, LoaderCircle, XCircle } from "lucide-vue-next";

import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Dialog } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import Combobox from "@/components/ComboBox.vue";
import FormContainerLayout from "@/components/common/dialog/FormContainerLayout.vue";
import InputIDR from "@/components/InputIDR.vue";
import DatePicker from "@/components/DatePicker.vue";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { formatIdr } from "@/lib/utils";

type Option = {
  label: string;
  value: number;
};

type OptionLoading = {
  account: boolean;
  accountTarget: boolean;
  category: boolean;
  budget: boolean;
};

interface Props {
  header: {
    title: string;
    description?: string;
  };
  defaultValues?: Transaction;
}

const props = defineProps<Props>();
const open = defineModel<boolean>("open");

const { defaultValues } = props;
const accountOptions = ref<Option[]>([]);
const accountTargetOptions = ref<Option[]>([]);
const categoryOptions = ref<Option[]>([]);
const budget = ref<Budget[]>();
const loading = reactive<OptionLoading>({
  account: false,
  accountTarget: false,
  category: false,
  budget: false,
});

const form = useForm({
  category_id: defaultValues?.category_id,
  account_id: defaultValues?.account_id,
  account_target_id: defaultValues?.account_target_id,
  type: defaultValues?.type ?? "income",
  amount: defaultValues?.amount,
  description: defaultValues?.description,
  transaction_date: defaultValues?.transaction_date
    ? new Date(defaultValues.transaction_date)
    : new Date(),
});

const fetchOptions = async (
  routeName: string,
  targetRef: Ref<any>,
  loadingKey: keyof typeof loading
) => {
  console.log(`Hi, im fetching... ${routeName}`);

  loading[loadingKey] = true;
  try {
    const response = await fetch(routeName);
    const { data } = await response.json();
    targetRef.value = data;
  } catch (error) {
    toast.error("Failed when fetching data, please try again later !");
    console.error(error);
  } finally {
    loading[loadingKey] = false;
  }
};

const handleClose = (value: boolean) => {
  form.reset();
  open.value = value;
};

const handleSubmit = () => {
  if (!defaultValues) {
    form.post(route("transaction.store"), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Transaction has successfully created.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  } else {
    form.put(route("transaction.update", { id: defaultValues.id }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("Success, Transaction has successfully updated.");
        handleClose(false);
      },
      onError: () => {
        toast.error("Failed, Something went wrong, please try again.");
      },
    });
  }
};

watch(open, (newValue) => {
  if (newValue) {
    if (props.defaultValues) {
      fetchOptions(route("account.options"), accountOptions, "account");

      fetchOptions(
        route("category.options", { type: props.defaultValues.type }),
        categoryOptions,
        "category"
      );

      if (props.defaultValues.type === "transfer") {
        fetchOptions(route("account.options"), accountTargetOptions, "accountTarget");
      }

      const values = {
        category_id: props.defaultValues.category_id,
        account_id: props.defaultValues.account_id,
        account_target_id: props.defaultValues.account_target_id,
        type: props.defaultValues.type,
        amount: props.defaultValues.amount,
        description: props.defaultValues.description,
        transaction_date: new Date(props.defaultValues.transaction_date),
      };

      form.defaults(values);
    } else {
      fetchOptions(
        route("account.options", { types: ["cash", "bank", "ewallet"] }),
        accountOptions,
        "account"
      );
      fetchOptions(
        route("category.options", { type: "income" }),
        categoryOptions,
        "category"
      );
    }
  }
});

watch(
  () => form.type,
  (newValue) => {
    if (!open.value) return;

    if (newValue !== "transfer") {
      fetchOptions(
        route("category.options", { type: newValue }),
        categoryOptions,
        "category"
      );
    } else {
      fetchOptions(route("account.options"), accountTargetOptions, "accountTarget");
    }
  }
);

watch(
  () => form.category_id,
  (newValue) => {
    if (!open.value) return;

    if (newValue && form.type === "expense") {
      budget.value = undefined;
      const currentMonth = new Date(form.transaction_date).toISOString().slice(0, 7);
      fetchOptions(
        route("budget.by-category", { category_id: newValue, month: currentMonth }),
        budget,
        "budget"
      );
    }
  }
);
</script>

<template>
  <Dialog :open="open" @update:open="handleClose">
    <FormContainerLayout :header="header">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
        <div class="grid gap-6">
          <div class="grid gap-2">
            <Label for="type">Transaction Type</Label>
            <Tabs v-model="form.type">
              <TabsList class="w-full rounded-sm">
                <TabsTrigger value="income"> Income </TabsTrigger>
                <TabsTrigger value="expense"> Expense </TabsTrigger>
                <TabsTrigger value="transfer"> Transfer </TabsTrigger>
              </TabsList>
            </Tabs>
            <InputError :message="form.errors.type" />
          </div>

          <div class="grid gap-2">
            <Label>Date</Label>
            <DatePicker v-model="form.transaction_date" />
            <InputError :message="form.errors.transaction_date" />
          </div>

          <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Input
              v-model="form.description"
              id="description"
              placeholder="Transaction Description"
            />
            <InputError :message="form.errors.description" />
          </div>

          <div class="grid gap-2">
            <Label for="amount">Amount</Label>
            <InputIDR v-model="form.amount" placeholder="Amount of Transaction" />
            <InputError :message="form.errors.amount" />
          </div>

          <div v-if="form.type !== 'transfer'" class="grid gap-2">
            <Label for="category">Category</Label>
            <Combobox
              v-model:value="form.category_id"
              :loading="loading.category"
              :options="categoryOptions"
              :disabled="!form.type || !categoryOptions.length || !form.amount"
              placeholder="Category of transaction"
            />
            <span
              v-if="loading.budget"
              class="text-muted-foreground text-sm inline-flex items-center gap-1"
            >
              <LoaderCircle class="h-4 w-4 animate-spin" />
              Checking Budget...
            </span>
            <span
              v-else-if="budget?.[0].amount && form.amount"
              :class="{
                'text-emerald-500': budget[0].amount >= form.amount,
                'text-destructive': budget[0].amount < form.amount,
              }"
              class="text-sm inline-flex items-center gap-1"
            >
              <component
                :is="budget[0].amount >= form.amount ? CheckCircle : XCircle"
                class="h-4 w-4"
              />
              {{
                budget[0].amount >= form.amount
                  ? `In Budget. Remaining : ${formatIdr(
                      Number(budget[0].amount) - Number(form.amount),
                      true
                    )}`
                  : `Budget Exceeded. Remaining : ${formatIdr(
                      Number(budget[0].amount) - Number(form.amount),
                      true
                    )}`
              }}
            </span>
          </div>

          <div class="grid gap-2">
            <Label for="account">{{
              form.type === "transfer" ? "From Account" : "Account"
            }}</Label>
            <Combobox
              v-model:value="form.account_id"
              :loading="loading.account"
              :options="accountOptions"
              :disabled="!accountOptions.length"
              placeholder="Account"
            />
            <InputError :message="form.errors.account_id" />
          </div>

          <div v-if="form.type === 'transfer'" class="grid gap-2">
            <Label for="account">To Account</Label>
            <Combobox
              v-model:value="form.account_target_id"
              :loading="loading.accountTarget"
              :options="accountTargetOptions"
              :disabled="!accountTargetOptions.length"
              placeholder="Transaction Type"
            />
            <InputError :message="form.errors.account_target_id" />
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
