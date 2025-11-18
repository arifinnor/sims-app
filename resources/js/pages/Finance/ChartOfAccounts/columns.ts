import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import ActionsCell from './ActionsCell.vue';

export interface Account {
    id: string;
    accountNumber: string;
    fullAccountNumber: string;
    name: string;
    type: string;
    category: string | null;
    parentAccountId?: string | null;
    balance: string;
    currency: string;
    status: string;
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

const formatBalance = (balance: string, currency: string): string => {
    const numBalance = parseFloat(balance);
    const formatted = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numBalance);

    return `Rp ${formatted}`;
};

const getTypeColor = (type: string): string => {
    const colors: Record<string, string> = {
        asset: 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100',
        liability: 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100',
        equity: 'bg-green-500/10 text-green-700 dark:bg-green-500/20 dark:text-green-100',
        revenue: 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
        expense: 'bg-orange-500/10 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
    };

    return colors[type] || 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
};

const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
        active: 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
        inactive: 'bg-yellow-500/10 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-100',
        archived: 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100',
    };

    return colors[status] || 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
};

export const createColumns = (
    onDeleteClick: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void,
    onRestoreClick?: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void,
    onForceDeleteClick?: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void,
): ColumnDef<Account>[] => [
    {
        accessorKey: 'accountNumber',
        header: 'Account Number',
        cell: ({ row }) => {
            return h('div', { class: 'font-medium' }, row.getValue('accountNumber'));
        },
    },
    {
        accessorKey: 'fullAccountNumber',
        header: 'Full Account Number',
        cell: ({ row }) => {
            return h('div', { class: 'text-muted-foreground font-mono text-sm' }, row.getValue('fullAccountNumber'));
        },
    },
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => {
            const account = row.original;
            return h(
                Link,
                {
                    href: AccountController.show.url(account.id),
                    class: 'font-medium text-primary underline-offset-4 transition hover:underline',
                },
                () => row.getValue('name'),
            );
        },
    },
    {
        accessorKey: 'type',
        header: 'Type',
        cell: ({ row }) => {
            const type = row.getValue('type') as string;
            return h(
                Badge,
                {
                    class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getTypeColor(type)}`,
                },
                () => type.charAt(0).toUpperCase() + type.slice(1),
            );
        },
    },
    {
        accessorKey: 'category',
        header: 'Category',
        cell: ({ row }) => {
            const category = row.getValue('category') as string | null;
            return h('div', { class: 'text-muted-foreground' }, category || '—');
        },
    },
    {
        accessorKey: 'balance',
        header: 'Balance',
        cell: ({ row }) => {
            const account = row.original;
            return h('div', { class: 'font-medium' }, formatBalance(account.balance, account.currency));
        },
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.getValue('status') as string;
            return h(
                Badge,
                {
                    class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getStatusColor(status)}`,
                },
                () => status.charAt(0).toUpperCase() + status.slice(1),
            );
        },
    },
    {
        accessorKey: 'deletedAt',
        header: 'Deleted Status',
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
            return h('div', { class: 'text-muted-foreground' }, '—');
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
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const account = row.original;
            return h(ActionsCell, {
                account,
                onDeleteClick,
                onRestoreClick,
                onForceDeleteClick,
            });
        },
    },
];

