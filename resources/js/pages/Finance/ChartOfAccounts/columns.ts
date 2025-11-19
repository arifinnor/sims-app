import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import ChartOfAccountController from '@/actions/App/Http/Controllers/Finance/ChartOfAccountController';
import ActionsCell from './ActionsCell.vue';

export interface ChartOfAccount {
    id: string;
    category_id: string | null;
    code: string;
    name: string;
    description: string | null;
    parent_id: string | null;
    account_type: string;
    normal_balance: string;
    is_posting: boolean;
    is_cash: boolean;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    category?: {
        id: string;
        name: string;
    } | null;
    parent?: {
        id: string;
        code: string;
        name: string;
    } | null;
    is_header: boolean;
    has_children: boolean;
}

const formatDateTime = (value: string): string =>
    new Date(value).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const getAccountTypeBadgeClass = (type: string): string => {
    const classes: Record<string, string> = {
        ASSET: 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100',
        LIABILITY: 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100',
        EQUITY: 'bg-purple-500/10 text-purple-700 dark:bg-purple-500/20 dark:text-purple-100',
        REVENUE: 'bg-green-500/10 text-green-700 dark:bg-green-500/20 dark:text-green-100',
        EXPENSE: 'bg-orange-500/10 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
    };
    return classes[type] || 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
};

export const createColumns = (
    onDeleteClick: (
        account: ChartOfAccount,
        submit: () => void,
        processing: () => boolean,
    ) => void,
    onRestoreClick?: (
        account: ChartOfAccount,
        submit: () => void,
        processing: () => boolean,
    ) => void,
    onForceDeleteClick?: (
        account: ChartOfAccount,
        submit: () => void,
        processing: () => boolean,
    ) => void,
): ColumnDef<ChartOfAccount>[] => [
    {
        accessorKey: 'code',
        header: 'Code',
        cell: ({ row }) => {
            const account = row.original;
            return h(
                Link,
                {
                    href: ChartOfAccountController.show.url(account.id),
                    class: 'font-mono text-primary underline-offset-4 transition hover:underline',
                },
                () => account.code,
            );
        },
    },
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => {
            const account = row.original;
            return h(
                'div',
                { class: 'flex items-center gap-2' },
                [
                    h(
                        Link,
                        {
                            href: ChartOfAccountController.show.url(account.id),
                            class: 'font-medium text-primary underline-offset-4 transition hover:underline',
                        },
                        () => account.name,
                    ),
                    account.is_header && h(
                        Badge,
                        {
                            class: 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100',
                        },
                        () => 'Header',
                    ),
                    account.is_cash && h(
                        Badge,
                        {
                            class: 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-yellow-500/10 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-100',
                        },
                        () => 'Cash',
                    ),
                ],
            );
        },
    },
    {
        accessorKey: 'account_type',
        header: 'Type',
        cell: ({ row }) => {
            const type = row.getValue('account_type') as string;
            return h(
                Badge,
                {
                    class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getAccountTypeBadgeClass(type)}`,
                },
                () => type,
            );
        },
    },
    {
        accessorKey: 'normal_balance',
        header: 'Normal Balance',
        cell: ({ row }) => {
            const balance = row.getValue('normal_balance') as string;
            return h(
                'div',
                { class: 'font-medium' },
                balance,
            );
        },
    },
    {
        accessorKey: 'category',
        header: 'Category',
        cell: ({ row }) => {
            const category = row.original.category;
            return h(
                'div',
                { class: 'text-muted-foreground' },
                category?.name || '—',
            );
        },
    },
    {
        accessorKey: 'is_active',
        header: 'Status',
        cell: ({ row }) => {
            const account = row.original;
            if (account.deleted_at) {
                return h(
                    Badge,
                    {
                        class: 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100',
                    },
                    () => 'Deleted',
                );
            }
            if (!account.is_active) {
                return h(
                    Badge,
                    {
                        class: 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100',
                    },
                    () => 'Inactive',
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
        accessorKey: 'created_at',
        header: 'Created',
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-muted-foreground' },
                formatDateTime(row.getValue('created_at')),
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

