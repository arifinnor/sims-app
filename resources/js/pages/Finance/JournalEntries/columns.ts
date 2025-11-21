import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import JournalEntryController from '@/actions/App/Http/Controllers/Finance/JournalEntryController';
import ActionsCell from './ActionsCell.vue';

export interface JournalEntry {
    id: string;
    referenceNumber: string;
    transactionDate: string;
    description: string | null;
    status: string;
    totalAmount: string;
    studentId: string | null;
    createdById: string | null;
    createdAt: string;
    updatedAt: string;
    transactionType?: {
        id: string;
        code: string;
        name: string;
    } | null;
    student?: {
        id: string;
        name: string;
        studentNumber: string;
    } | null;
    createdBy?: {
        id: string;
        name: string;
        email: string;
    } | null;
}

const formatDateTime = (value: string): string =>
    new Date(value).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

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
        case 'DRAFT':
            return 'bg-yellow-500/10 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-100';
        case 'POSTED':
            return 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100';
        case 'VOID':
            return 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100';
        default:
            return 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
    }
};

export const createColumns = (
    onVoidClick: (entry: JournalEntry) => void,
): ColumnDef<JournalEntry>[] => [
    {
        accessorKey: 'referenceNumber',
        header: 'Reference',
        cell: ({ row }) => {
            const entry = row.original;
            return h(
                Link,
                {
                    href: JournalEntryController.show.url(entry.id),
                    class: 'font-mono text-primary underline-offset-4 transition hover:underline',
                },
                () => row.getValue('referenceNumber'),
            );
        },
    },
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
                    class: 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100',
                },
                () => transactionType.name,
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
            return h('div', { class: 'font-medium' }, student.name);
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
            const entry = row.original;
            return h(ActionsCell, {
                entry,
                onVoidClick,
            });
        },
    },
];

