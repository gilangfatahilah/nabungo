import { Category } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import RowActions from './RowActions.vue';
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { ArrowDownCircle, ArrowUpCircle } from 'lucide-vue-next';

export const getTypeLabel = (type: Category['type']): BadgeVariants['variant'] => {
  if (type === 'income') {
    return 'a-success'
  } else {
    return 'a-error'
  }
}

export const columns: ColumnDef<Category>[] = [
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
      const type = row.getValue('type') as Category['type'];
      const icon = type === 'income' ? ArrowDownCircle : ArrowUpCircle;

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
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h(RowActions, { row: row.original }),
  },
];
