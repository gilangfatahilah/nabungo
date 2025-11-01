import { AccountHistory } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import { formatIdr } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import DetailDialog from './DetailDialog.vue';
import { ArrowDownCircle, ArrowUpCircle } from 'lucide-vue-next';

export const columns: ColumnDef<AccountHistory>[] = [
  {
    accessorKey: 'transaction',
    header: ({ column }) => h(DataTableColumnHeader, { class: "ml-4", column, title: 'Date' }),
    cell: ({ row }) => {
      const transaction = row.getValue('transaction') as AccountHistory['transaction'];

      if (!transaction) {
        return h('span', { class: 'max-w-[500px] truncate font-medium' }, '-');
      }

      const rawDate = new Date(transaction.transaction_date);
      const date = Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      }).format(rawDate)

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium ml-4' },
        date,
      );
    },
  },
  {
    accessorKey: 'transaction',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Time' }),
    cell: ({ row }) => {
      const transaction = row.getValue('transaction') as AccountHistory['transaction'];

      if (!transaction) {
        return h('span', { class: 'max-w-[500px] truncate font-medium' }, '-');
      }

      const rawDate = new Date(transaction.transaction_date);
      const date = Intl.DateTimeFormat("en-US", {
        hour: "2-digit",
        minute: "2-digit",
      }).format(rawDate)

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        date,
      );
    },
  },
  {
    accessorKey: 'account',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Account' }),

    cell: ({ row }) => {
      const account = row.getValue('account') as AccountHistory['account'];

      if (!account) {
        return h('span', { class: 'max-w-[500px] truncate font-medium' }, '-');
      }

      return h(
        Badge,
        { class: 'max-w-[500px] truncate font-medium rounded-sm', variant: 'secondary' },
        account.name,
      );
    },
  },
  {
    accessorKey: 'notes',
    header: "Notes",

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        row.getValue('notes') ?? "-",
      );
    },
  },
  {
    accessorKey: 'type',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Type' }),

    cell: ({ row }) => {
      const type = row.getValue('type') as AccountHistory['type'];
      const icon = type === 'in' ? ArrowDownCircle : ArrowUpCircle;

      return h(
        Badge,
        { class: 'capitalize flex items-center gap-1', variant: type === 'in' ? "a-success" : "a-error" },
        [
          h(icon, { size: 14, class: 'mr-1' }),
          type,
        ]
      );
    },
  },
  {
    accessorKey: 'amount',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount' }),

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        formatIdr(Number(row.getValue('amount')), true),
      );
    },
  },
  {
    id: 'detail',
    header: 'Detail',
    cell: ({ row }) => h(DetailDialog, { data: row.original }),
  },
];
