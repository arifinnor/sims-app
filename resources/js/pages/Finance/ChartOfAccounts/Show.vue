<script setup lang="ts">
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface AccountDetail {
    id: string;
    accountNumber: string;
    fullAccountNumber: string;
    name: string;
    type: string;
    category: string | null;
    parentAccountId: string | null;
    parentAccount: AccountDetail | null;
    balance: string;
    currency: string;
    status: string;
    description: string | null;
    createdAt: string;
    updatedAt: string;
    deletedAt: string | null;
}

interface Props {
    account: AccountDetail;
}

const props = defineProps<Props>();

const formatBalance = (balance: string, currency: string): string => {
    const numBalance = parseFloat(balance);
    const formatted = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numBalance);

    return `Rp ${formatted}`;
};

const formatDateTime = (value: string): string =>
    new Date(value).toLocaleString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chart of Accounts',
        href: AccountController.index().url,
    },
    {
        title: props.account.name,
        href: AccountController.show.url(props.account.id),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.account.name" />

        <div class="space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="props.account.name"
                        :description="`Account details for ${props.account.fullAccountNumber}`"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button as-child variant="default" class="w-full md:w-auto">
                        <Link :href="AccountController.edit.url(props.account.id)">
                            Edit account
                        </Link>
                    </Button>
                    <Button as-child variant="secondary" class="w-full md:w-auto">
                        <Link :href="AccountController.index().url">
                            Back to accounts
                        </Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <h3 class="text-lg font-semibold">Account Information</h3>
                </CardHeader>
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Account Number</p>
                        <p class="text-base font-mono">{{ props.account.accountNumber }}</p>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Full Account Number</p>
                        <p class="text-base font-mono">{{ props.account.fullAccountNumber }}</p>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Name</p>
                        <p class="text-base">{{ props.account.name }}</p>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Type</p>
                        <Badge
                            :class="`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold w-fit ${getTypeColor(props.account.type)}`"
                        >
                            {{ props.account.type.charAt(0).toUpperCase() + props.account.type.slice(1) }}
                        </Badge>
                    </div>

                    <div v-if="props.account.category" class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Category</p>
                        <p class="text-base">{{ props.account.category }}</p>
                    </div>

                    <div v-if="props.account.parentAccount" class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Parent Account</p>
                        <Link
                            :href="AccountController.show.url(props.account.parentAccount.id)"
                            class="text-primary underline-offset-4 transition hover:underline"
                        >
                            {{ props.account.parentAccount.fullAccountNumber }} - {{ props.account.parentAccount.name }}
                        </Link>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Balance</p>
                        <p class="text-base font-semibold">{{ formatBalance(props.account.balance, props.account.currency) }}</p>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Currency</p>
                        <p class="text-base">{{ props.account.currency }}</p>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Status</p>
                        <Badge
                            :class="`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold w-fit ${getStatusColor(props.account.status)}`"
                        >
                            {{ props.account.status.charAt(0).toUpperCase() + props.account.status.slice(1) }}
                        </Badge>
                    </div>

                    <div v-if="props.account.description" class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Description</p>
                        <p class="text-base whitespace-pre-wrap">{{ props.account.description }}</p>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Created</p>
                        <p class="text-base">{{ formatDateTime(props.account.createdAt) }}</p>
                    </div>

                    <div class="grid gap-2">
                        <p class="text-sm font-medium text-muted-foreground">Last Updated</p>
                        <p class="text-base">{{ formatDateTime(props.account.updatedAt) }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
