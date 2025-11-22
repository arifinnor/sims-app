import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Badge } from '@/components/ui/badge';
import TransactionController from '@/actions/App/Http/Controllers/Finance/TransactionController';
import ActionsCell from './ActionsCell.vue';

export interface Transaction {
    id: string;
    referenceNumber: string;
    transactionDate: string;
    description: string | null;
    status: string;
    totalAmount: string;
    studentId: string | null;
    createdAt: string;
    updatedAt: string;
    transactionType?: {
        id: string;
        code: string;
        name: string;
        category?: string;
    } | null;
    student?: {
        id: string;
        name: string;
        studentNumber: string;
    } | null;
}

const formatDate = (value: string): string =>
    new Date(value).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });

const formatCurrency = (value: string): string => {
    const num = parseFloat(value);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

const getStatusBadgeClass = (status: string): string => {
    switch (status) {
        case 'POSTED':
            return 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100';
        case 'VOID':
            return 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100 line-through';
        case 'DRAFT':
            return 'bg-yellow-500/10 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-100';
        default:
            return 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
    }
};

const getCategoryBadgeClass = (category: string | undefined): string => {
    if (!category) {
        return 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100';
    }

    switch (category.toUpperCase()) {
        case 'INCOME':
            return 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100';
        case 'EXPENSE':
            return 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100';
        case 'TRANSFER':
            return 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100';
        case 'BILLING':
            return 'bg-purple-500/10 text-purple-700 dark:bg-purple-500/20 dark:text-purple-100';
        case 'PAYROLL':
            return 'bg-orange-500/10 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100';
        case 'ASSET':
            return 'bg-cyan-500/10 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-100';
        case 'LIABILITY':
            return 'bg-amber-500/10 text-amber-700 dark:bg-amber-500/20 dark:text-amber-100';
        default:
            return 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100';
    }
};

export const createColumns = (
    onVoidClick: (entry: Transaction) => void,
): ColumnDef<Transaction>[] => [
    {
        accessorKey: 'transactionDate',
        header: 'Date',
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-muted-foreground' },
                formatDate(row.getValue('transactionDate')),
            );
        },
    },
    {
        accessorKey: 'referenceNumber',
        header: 'Ref #',
        cell: ({ row }) => {
            const entry = row.original;
            return h(
                'div',
                { class: 'font-mono text-sm' },
                row.getValue('referenceNumber'),
            );
        },
    },
    {
        accessorKey: 'transactionType',
        header: 'Type',
        cell: ({ row }) => {
            const transactionType = row.original.transactionType;
            if (!transactionType) {
                return h('div', { class: 'text-muted-foreground' }, '—');
            }
            return h(
                Badge,
                {
                    class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getCategoryBadgeClass(transactionType.category)}`,
                },
                () => transactionType.name,
            );
        },
    },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }) => {
            const description = row.getValue('description') as string | null;
            return h(
                'div',
                { class: 'max-w-md truncate text-sm' },
                description || '—',
            );
        },
    },
    {
        accessorKey: 'student',
        header: 'Student',
        cell: ({ row }) => {
            const student = row.original.student;
            if (!student) {
                return h('div', { class: 'text-muted-foreground' }, '—');
            }
            return h('div', { class: 'font-medium text-sm' }, student.name);
        },
    },
    {
        accessorKey: 'totalAmount',
        header: 'Amount',
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-right font-medium' },
                formatCurrency(row.getValue('totalAmount')),
            );
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
                    class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getStatusBadgeClass(status)}`,
                },
                () => status,
            );
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const entry = row.original;
            return h(ActionsCell, {
                entry,
                onVoidClick,
            });
        },
    },
];


