import { Budget } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import { dateToMonth, formatIdr } from '@/lib/utils';
import { Progress } from '@/components/ui/progress';
import RowActions from './RowActions.vue';

export const columns: ColumnDef<Budget>[] = [
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
    accessorKey: 'category',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Category' }),

    cell: ({ row }) => {
      const category = row.getValue('category') as Budget['category'];

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        category.name,
      );
    },
  },
  {
    accessorKey: 'month',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Month' }),

    cell: ({ row }) => {
      const date = row.getValue('month') as Budget['month'];

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        dateToMonth(date),
      );
    },
  },
  {
    accessorKey: 'amount',
    header: 'Amount',

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        formatIdr(row.getValue('amount'), true),
      );
    },
  },
  {
    accessorKey: 'expenses',
    header: 'Expenses',

    cell: ({ row }) => {
      const progress = Math.floor(Math.random() * 200000) + 1;;
      const limit = row.getValue('amount') as Budget['amount'];
      const percentage = Math.round(limit > 0 ? (progress / limit) * 100 : 0);

      return h(
        Progress,
        { bgColor: percentage <= 100 ? 'bg-primary' : 'bg-[#f43f5e]', modelValue: percentage, class: 'w-3/4' },
      );
    },
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h(RowActions, { row: row.original }),
  },
];
