import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Updater } from '@tanstack/vue-table';
import type { Ref } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function valueUpdater<T extends Updater<any>>(updaterOrValue: T, ref: Ref) {
    ref.value = typeof updaterOrValue === 'function' ? updaterOrValue(ref.value) : updaterOrValue;
}

export function formatIdr(value: number, withPrefix: boolean = false): string {
    const formatted = value.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
    });

    if (withPrefix) {
        return formatted;
    } else {
        return formatted.replace(/^Rp\s?/, '');
    }
}

export function dateToMonth(value: string): string {
    const date = new Date(value);

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "long",
    }).format(date);
}

export function toDateOnly(date: Date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, "0"); // getMonth() is 0-indexed
    const day = date.getDate().toString().padStart(2, "0");

    return `${year}-${month}-${day}`;
};
