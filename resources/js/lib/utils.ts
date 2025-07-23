import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatIdr(value: number, withPrefix: boolean = false): string {
  const formatted = value.toLocaleString('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  });

  if (withPrefix) {
    return formatted;
  } else {
    return formatted.replace(/^Rp\s?/, '');
  }
}

