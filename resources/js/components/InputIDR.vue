<script setup lang="ts">
import { ref, watch } from "vue";
import { Input } from "@/components/ui/input";

const props = defineProps<{
  modelValue?: number | null;
  placeholder?: string;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", value: number | null): void;
}>();

// Local formatted string
const formatted = ref("");

// Format number ke Rp xxx.xxx
function formatRupiah(value: number | null) {
  if (value === null || isNaN(value)) return "";
  return "Rp " + value.toLocaleString("id-ID");
}

// Parse dari input string ke angka
function parseRupiah(value: string): number | null {
  const cleaned = value.replace(/[^\d]/g, "");
  return cleaned ? parseInt(cleaned) : null;
}

// Sync saat modelValue luar berubah
watch(
  () => props.modelValue,
  (val) => {
    formatted.value = formatRupiah(val ?? null);
  },
  { immediate: true }
);

// Handler saat user mengetik
function onInput(e: Event) {
  const target = e.target as HTMLInputElement;
  const numericValue = parseRupiah(target.value);
  emit("update:modelValue", numericValue);
  formatted.value = formatRupiah(numericValue);
}
</script>

<template>
  <Input
    v-model="formatted"
    @input="onInput"
    :placeholder="placeholder ?? 'Masukan jumlah IDR'"
  />
</template>
