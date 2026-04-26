import { Debt } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import { formatIdr } from '@/lib/utils';
import { Progress } from '@/components/ui/progress';
import RowActions from './RowActions.vue';
import { Badge, BadgeVariants } from '@/components/ui/badge';

export const getStatusVariant = (status: Debt['status']): BadgeVariants['variant'] => {
  switch (status) {
    case 'paid': return 'a-success';
    case 'partial': return 'a-warning';
    default: return 'a-error';
  }
};

export const getTypeVariant = (type: Debt['type']): BadgeVariants['variant'] => {
  return type === 'receivable' ? 'a-info' : 'a-neutral';
};

export const columns: ColumnDef<Debt>[] = [
  {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        modelValue: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
        'onUpdate:modelValue': (value) => table.toggleAllPageRowsSelected(!!value),
        ariaLabel: 'Select all',
        class: 'translate-y-0.5',
      }),
    cell: ({ row }) =>
      h(Checkbox, {
        modelValue: row.getIsSelected(),
        'onUpdate:modelValue': (value) => row.toggleSelected(!!value),
        ariaLabel: 'Select row',
        class: 'translate-y-0.5',
      }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'title',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Title' }),
    cell: ({ row }) =>
      h('span', { class: 'max-w-[300px] truncate font-medium' }, row.getValue('title')),
  },
  {
    accessorKey: 'type',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Type' }),
    cell: ({ row }) => {
      const type = row.getValue<Debt['type']>('type');
      return h(Badge, { variant: getTypeVariant(type), class: 'capitalize' }, () => type === 'debt' ? 'Debt' : 'Receivable');
    },
  },
  {
    accessorKey: 'amount',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount' }),
    cell: ({ row }) =>
      h('span', { class: 'font-medium' }, formatIdr(Number(row.getValue('amount')), true)),
  },
  {
    accessorKey: 'progress',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Progress' }),
    cell: ({ row }) => {
      const debt = row.original;
      return h('div', { class: 'flex flex-col gap-1 min-w-[120px]' }, [
        h('span', { class: 'text-xs text-muted-foreground' }, `${debt.progress}%`),
        h(Progress, { modelValue: debt.progress }),
      ]);
    },
  },
  {
    accessorKey: 'status',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),
    cell: ({ row }) => {
      const status = row.getValue<Debt['status']>('status');
      return h(Badge, { variant: getStatusVariant(status), class: 'capitalize' }, () => status);
    },
  },
  {
    accessorKey: 'formatted_due_date',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Due Date' }),
    cell: ({ row }) =>
      h('span', { class: 'text-sm' }, row.getValue('formatted_due_date') ?? '-'),
  },
  {
    id: 'actions',
    cell: ({ row }) => h(RowActions, { row: row.original }),
    enableHiding: false,
  },
];
