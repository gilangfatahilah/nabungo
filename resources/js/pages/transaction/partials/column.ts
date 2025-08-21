import { Transaction } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import { formatIdr } from '@/lib/utils';
import RowActions from './RowActions.vue';
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { ArrowDownCircle, ArrowLeftRight, ArrowUpCircle } from 'lucide-vue-next';

export const getTypeLabel = (type: Transaction['type']): BadgeVariants['variant'] => {
  if (type === 'income') {
    return 'a-success'
  } else if (type === 'expense') {
    return 'a-error'
  } else {
    return 'a-info'
  }
}

export const columns: ColumnDef<Transaction>[] = [
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
    accessorKey: 'transaction_date',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Date' }),

    cell: ({ row }) => {
      const rawDate = new Date(row.getValue('transaction_date'));
      const date = Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric"
      }).format(rawDate)

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        date,
      );
    },
  },
  {
    accessorKey: 'type',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Type' }),

    cell: ({ row }) => {
      const type = row.getValue('type') as Transaction['type'];
      const icon = type === 'income' ? ArrowDownCircle : type === 'expense' ? ArrowUpCircle : ArrowLeftRight;

      return h(
        Badge,
        { class: 'capitalize flex items-center gap-1', variant: getTypeLabel(type) },
        [
          h(icon, { size: 14, class: 'mr-1' }),
          type,
        ]
      );
    },
  },
  {
    accessorKey: 'description',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Description' }),

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        row.getValue('description'),
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
    accessorKey: 'category',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Category' }),

    cell: ({ row }) => {
      const category = row.getValue('category') as Transaction['category'];

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        category?.name ?? "-",
      );
    },
  },
  {
    accessorKey: 'account',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Account' }),

    cell: ({ row }) => {
      const account = row.getValue('account') as Transaction['account'];

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        account.name,
      );
    },
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h(RowActions, { row: row.original }),
  },
];
