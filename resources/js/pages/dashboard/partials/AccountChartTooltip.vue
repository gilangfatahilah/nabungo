<script setup lang="ts">
import { computed } from "vue"
import { cn } from "@/lib/utils"

interface Props {
    payload?: {
        account?: string;
        balance?: number;
        percentage?: number;
        type?: string;
    };
    color?: string;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    payload: () => ({}),
})

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const accountName = computed(() => props.payload?.account || 'Unknown')
const balance = computed(() => props.payload?.balance || 0)
const percentage = computed(() => props.payload?.percentage || 0)
const accountType = computed(() => {
    const typeMap: Record<string, string> = {
        'cash': 'Tunai',
        'bank': 'Bank',
        'ewallet': 'E-Wallet',
        'asset': 'Aset',
        'liability': 'Liabilitas',
    }
    return typeMap[props.payload?.type || ''] || props.payload?.type || ''
})
</script>

<template>
    <div :class="cn(
        'border-border/50 bg-background grid min-w-[10rem] items-start gap-1.5 rounded-lg border px-2.5 py-2 text-xs shadow-xl',
        props.class,
    )">
        <div class="flex items-center gap-2">
            <div class="h-2.5 w-2.5 shrink-0 rounded-[2px]" :style="{ backgroundColor: color }" />
            <span class="font-medium text-foreground">{{ accountName }}</span>
        </div>
        <div class="grid gap-1 pl-4">
            <div class="flex justify-between gap-4">
                <span class="text-muted-foreground">Saldo</span>
                <span class="font-mono font-medium tabular-nums text-foreground">
                    {{ formatCurrency(balance) }}
                </span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-muted-foreground">Persentase</span>
                <span class="font-mono font-medium tabular-nums text-foreground">
                    {{ percentage }}%
                </span>
            </div>
            <div v-if="accountType" class="flex justify-between gap-4">
                <span class="text-muted-foreground">Tipe</span>
                <span class="font-medium text-foreground capitalize">
                    {{ accountType }}
                </span>
            </div>
        </div>
    </div>
</template>
