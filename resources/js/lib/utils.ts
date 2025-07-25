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

