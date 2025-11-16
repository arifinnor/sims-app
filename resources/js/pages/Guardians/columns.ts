import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import GuardianController from '@/actions/App/Http/Controllers/GuardianController';
import ActionsCell from './ActionsCell.vue';

export interface Guardian {
    id: string;
    name: string;
    email: string | null;
    phone: string | null;
    relationship: string | null;
    address: string | null;
    createdAt: string;
    updatedAt: string;
    deletedAt: string | null;
}

const formatDateTime = (value: string): string =>
    new Date(value).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

export const createColumns = (
    onDeleteClick: (
        guardian: Guardian,
        submit: () => void,
        processing: () => boolean,
    ) => void,
    onRestoreClick?: (
        guardian: Guardian,
        submit: () => void,
        processing: () => boolean,
    ) => void,
    onForceDeleteClick?: (
        guardian: Guardian,
        submit: () => void,
        processing: () => boolean,
    ) => void,
): ColumnDef<Guardian>[] => [
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => {
            return h('div', { class: 'font-medium' }, row.getValue('name'));
        },
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => {
            const guardian = row.original;
            const email = row.getValue('email') as string | null;
            if (!email) {
                return h('div', { class: 'text-muted-foreground' }, '—');
            }
            return h(
                Link,
                {
                    href: GuardianController.show.url(guardian.id),
                    class: 'text-primary underline-offset-4 transition hover:underline',
                },
                () => email,
            );
        },
    },
    {
        accessorKey: 'phone',
        header: 'Phone',
        cell: ({ row }) => {
            const phone = row.getValue('phone') as string | null;
            return h('div', { class: 'text-muted-foreground' }, phone || '—');
        },
    },
    {
        accessorKey: 'relationship',
        header: 'Relationship',
        cell: ({ row }) => {
            const relationship = row.getValue('relationship') as string | null;
            return h('div', { class: 'text-muted-foreground' }, relationship || '—');
        },
    },
    {
        accessorKey: 'deletedAt',
        header: 'Status',
        cell: ({ row }) => {
            const deletedAt = row.getValue('deletedAt') as string | null;
            if (deletedAt) {
                return h(
                    Badge,
                    {
                        class: 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100',
                    },
                    () => 'Deleted',
                );
            }
            return h(
                Badge,
                {
                    class: 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                },
                () => 'Active',
            );
        },
    },
    {
        accessorKey: 'createdAt',
        header: 'Created',
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-muted-foreground' },
                formatDateTime(row.getValue('createdAt')),
            );
        },
    },
    {
        accessorKey: 'updatedAt',
        header: 'Updated',
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-muted-foreground' },
                formatDateTime(row.getValue('updatedAt')),
            );
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const guardian = row.original;
            return h(ActionsCell, {
                guardian,
                onDeleteClick,
                onRestoreClick,
                onForceDeleteClick,
            });
        },
    },
];

