import { ActivityLog } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/common/data-table/DataTableColumnHeader.vue';
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { PlusCircle, SquarePen, Trash2 } from 'lucide-vue-next';
import DetailDialog from './DetailDialog.vue';

export const getTypeLabel = (type: ActivityLog['event']): BadgeVariants['variant'] => {
  let variant: BadgeVariants['variant'];

  switch (type) {
    case 'created':
      variant = 'a-success'
      break;
    case 'deleted':
      variant = 'a-error'
      break;
    default:
      variant = 'a-info'
  }

  return variant;
}

export const getIconType = (type: ActivityLog['event']) => {
  switch (type) {
    case 'created':
      return PlusCircle;
    case 'deleted':
      return Trash2;
    default:
      return SquarePen;
  }
}

export const columns: ColumnDef<ActivityLog>[] = [
  {
    accessorKey: 'created_at',
    header: ({ column }) => h(DataTableColumnHeader, { class: "ml-4", column, title: 'Date' }),
    cell: ({ row }) => {
      const createdAt = row.getValue('created_at') as ActivityLog['created_at'];

      if (!createdAt) {
        return h('span', { class: 'max-w-[500px] truncate font-medium' }, '-');
      }

      const rawDate = new Date(createdAt);
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
    id: 'time',
    accessorKey: 'created_at',
    header: ({ column }) => h(DataTableColumnHeader, { class: "ml-4", column, title: 'Time' }),
    cell: ({ row }) => {
      const createdAt = row.getValue('created_at') as ActivityLog['created_at'];

      if (!createdAt) {
        return h('span', { class: 'max-w-[500px] truncate font-medium' }, '-');
      }

      const rawDate = new Date(createdAt);
      const date = Intl.DateTimeFormat("en-US", {
        hour: "2-digit",
        minute: "2-digit",
      }).format(rawDate)

      return h(
        'span',
        { class: 'max-w-[500px] truncate font-medium ml-4' },
        date,
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
    accessorKey: 'event',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Event' }),

    cell: ({ row }) => {
      const event = row.getValue('event') as ActivityLog['event'];

      return h(
        Badge,
        { class: 'capitalize flex items-center gap-1', variant: getTypeLabel(event) },
        [
          h(getIconType(event), { size: 14, class: 'mr-1' }),
          event,
        ]
      );
    },
  },
  {
    accessorKey: 'subject_type',
    header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Subject' }),

    cell: ({ row }) => {
      const str = row.getValue('subject_type') as ActivityLog['subject_type'] ?? "-";
      const parts = str.split("\\");
      const subject = parts[parts.length - 1];

      return h(
        Badge,
        { class: 'max-w-[500px] truncate font-medium', variant: 'secondary' },
        subject,
      );
    },
  },
  {
    id: 'detail',
    header: 'Changes',
    cell: ({ row }) => h(DetailDialog, { log: row.original }),
  },
];
