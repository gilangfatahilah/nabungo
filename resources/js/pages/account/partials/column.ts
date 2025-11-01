import { Account } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import { formatIdr } from '@/lib/utils';
import RowActions from './RowActions.vue';
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { CreditCard, HandCoins, Landmark, Wallet } from 'lucide-vue-next';

export const getTypeLabel = (type: Account['type']): BadgeVariants['variant'] => {
  let variant: BadgeVariants['variant'];

  switch (type) {
    case 'bank':
      variant = 'a-warning'
      break;
    case 'cash':
      variant = 'a-success'
      break;
    case 'liability':
      variant = 'a-error'
      break;
    default:
      variant = 'a-info'
  }

  return variant;
}

export const getIconType = (type: Account['type']) => {
  switch (type) {
    case 'cash':
      return Wallet;
    case 'bank':
      return Landmark;
    case 'ewallet':
      return CreditCard;
    case 'goal':
      return HandCoins;
    default:
      return Wallet;
  }
}

export const columns: ColumnDef<Account>[] = [
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
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Name' }),

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        row.getValue('name'),
      );
    },
  },
  {
    accessorKey: 'type',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Type' }),

    cell: ({ row }) => {
      const type = row.getValue('type') as Account['type'];

      return h(
        Badge,
        { class: 'capitalize flex items-center gap-1', variant: getTypeLabel(type) },
        [
          h(getIconType(type), { size: 14, class: 'mr-1' }),
          type,
        ]
      );
    },
  },
  {
    accessorKey: 'balance',
    header: 'Balance',

    cell: ({ row }) => {
      return h('span', { class: 'max-w-[500px] truncate font-medium' }, formatIdr(Number(row.getValue('balance')) ?? 0, true));
    },
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h(RowActions, { row: row.original }),
  },
];
