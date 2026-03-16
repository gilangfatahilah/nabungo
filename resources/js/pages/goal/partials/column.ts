import { Goal } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import { formatIdr } from '@/lib/utils';
import { Progress } from '@/components/ui/progress';
import RowActions from './RowActions.vue';
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { AlarmClock, AlarmClockCheck, AlarmClockOff } from 'lucide-vue-next';

export const getTypeLabel = (type: Goal['status']): BadgeVariants['variant'] => {
  let variant: BadgeVariants['variant'];

  switch (type) {
    case 'cancelled':
      variant = 'a-error'
      break;
    case 'achieved':
      variant = 'a-success'
      break;
    default:
      variant = 'a-info'
  }

  return variant;
}

export const getIconType = (type: Goal['status']) => {
  switch (type) {
    case 'cancelled':
      return AlarmClockOff;
    case 'achieved':
      return AlarmClockCheck;
    default:
      return AlarmClock;
  }
}

export const columns: ColumnDef<Goal>[] = [
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

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        row.getValue('title')
      );
    },
  },
  {
    accessorKey: 'deadline',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Deadline' }),

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        row.getValue('deadline'),
      );
    },
  },
  {
    accessorKey: 'target_amount',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Target' }),

    cell: ({ row }) => {
      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium' },
        formatIdr(Number(row.getValue('target_amount')), true),
      );
    },
  },
  {
    accessorKey: 'status',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),

    cell: ({ row }) => {
      const status = row.getValue('status') as Goal['status'];

      return h(
        Badge,
        { class: 'capitalize flex items-center gap-1', variant: getTypeLabel(status) },
        [
          h(getIconType(status), { size: 14, class: 'mr-1' }),
          status,
        ]
      );
    },
  },
  {
    accessorKey: 'progress',
    header: 'Progress',

    cell: ({ row }) => {
      const percentage = row.getValue('progress') as number;

      return h(
        Progress,
        { bgColor: percentage < 100 ? 'bg-primary' : 'bg-[#f43f5e]', modelValue: percentage, class: 'w-3/4', tooltip: `${percentage}% saved` },
      );
    },
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h(RowActions, { row: row.original }),
  },
];
