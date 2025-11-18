<script setup lang="ts">
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref, watch, onMounted } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import TreeView from './TreeView.vue';
import type { Account } from './columns';

interface Props {
    accounts: Account[];
}

const props = defineProps<Props>();

const searchQuery = ref<string>('');
const withTrashedFilter = ref<string>('none');
const typeFilter = ref<string>('__all__');
const statusFilter = ref<string>('__all__');
const isInitialMount = ref(true);

const updateFilters = useDebounceFn(() => {
    const url = new URL(AccountController.index().url, window.location.origin);
    if (searchQuery.value) {
        url.searchParams.set('search', searchQuery.value);
    } else {
        url.searchParams.delete('search');
    }
    if (withTrashedFilter.value !== 'none') {
        url.searchParams.set('with_trashed', withTrashedFilter.value);
    } else {
        url.searchParams.delete('with_trashed');
    }
    if (typeFilter.value && typeFilter.value !== '__all__') {
        url.searchParams.set('type', typeFilter.value);
    } else {
        url.searchParams.delete('type');
    }
    if (statusFilter.value && statusFilter.value !== '__all__') {
        url.searchParams.set('status', statusFilter.value);
    } else {
        url.searchParams.delete('status');
    }

    router.visit(url.pathname + url.search, {
        only: ['accounts'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch([searchQuery, withTrashedFilter, typeFilter, statusFilter], () => {
    if (!isInitialMount.value) {
        updateFilters();
    }
});

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    searchQuery.value = urlParams.get('search') || '';
    withTrashedFilter.value = urlParams.get('with_trashed') || 'none';
    typeFilter.value = urlParams.get('type') || '__all__';
    statusFilter.value = urlParams.get('status') || '__all__';
    isInitialMount.value = false;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chart of Accounts',
        href: AccountController.index().url,
    },
];

const isDeleteDialogOpen = ref(false);
const isRestoreDialogOpen = ref(false);
const isForceDeleteDialogOpen = ref(false);
const pendingDeleteAccount = ref<Account | null>(null);
const pendingDeleteAction = ref<{
    submit: () => void;
    processing: () => boolean;
} | null>(null);
const pendingRestoreAction = ref<{
    submit: () => void;
    processing: () => boolean;
} | null>(null);
const pendingForceDeleteAction = ref<{
    submit: () => void;
    processing: () => boolean;
} | null>(null);

const openDeleteDialog = (
    account: Account,
    submit: () => void,
    processing: () => boolean,
) => {
    pendingDeleteAccount.value = account;
    pendingDeleteAction.value = {
        submit,
        processing,
    };
    isDeleteDialogOpen.value = true;
};

const openRestoreDialog = (
    account: Account,
    submit: () => void,
    processing: () => boolean,
) => {
    pendingDeleteAccount.value = account;
    pendingRestoreAction.value = {
        submit,
        processing,
    };
    isRestoreDialogOpen.value = true;
};

const openForceDeleteDialog = (
    account: Account,
    submit: () => void,
    processing: () => boolean,
) => {
    pendingDeleteAccount.value = account;
    pendingForceDeleteAction.value = {
        submit,
        processing,
    };
    isForceDeleteDialogOpen.value = true;
};

const confirmDelete = () => {
    if (!pendingDeleteAction.value) {
        return;
    }

    pendingDeleteAction.value.submit();
    isDeleteDialogOpen.value = false;
};

const confirmRestore = () => {
    if (!pendingRestoreAction.value) {
        return;
    }

    pendingRestoreAction.value.submit();
    isRestoreDialogOpen.value = false;
};

const confirmForceDelete = () => {
    if (!pendingForceDeleteAction.value) {
        return;
    }

    pendingForceDeleteAction.value.submit();
    isForceDeleteDialogOpen.value = false;
};

const cancelDelete = () => {
    isDeleteDialogOpen.value = false;
};

const cancelRestore = () => {
    isRestoreDialogOpen.value = false;
};

const cancelForceDelete = () => {
    isForceDeleteDialogOpen.value = false;
};

watch(isDeleteDialogOpen, (open) => {
    if (!open) {
        pendingDeleteAccount.value = null;
        pendingDeleteAction.value = null;
    }
});

watch(isRestoreDialogOpen, (open) => {
    if (!open) {
        pendingDeleteAccount.value = null;
        pendingRestoreAction.value = null;
    }
});

watch(isForceDeleteDialogOpen, (open) => {
    if (!open) {
        pendingDeleteAccount.value = null;
        pendingForceDeleteAction.value = null;
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Chart of Accounts" />

        <div>
            <div class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        title="Chart of Accounts"
                        description="Manage your financial accounts and their hierarchical structure"
                    />
                </div>
                <Button as-child class="w-full md:w-auto">
                    <Link :href="AccountController.create().url">
                        Create account
                    </Link>
                </Button>
            </div>
            <div class="space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="w-full max-w-sm">
                        <Input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search by account number or name..."
                            class="w-full"
                        />
                    </div>
                    <div class="w-full sm:w-auto">
                        <Select v-model="typeFilter">
                            <SelectTrigger class="w-full sm:w-[180px]">
                                <SelectValue placeholder="All types" />
                            </SelectTrigger>
                            <SelectContent class="min-w-[180px]">
                                <SelectItem value="__all__" class="whitespace-nowrap">All Types</SelectItem>
                                <SelectItem value="asset" class="whitespace-nowrap">Asset</SelectItem>
                                <SelectItem value="liability" class="whitespace-nowrap">Liability</SelectItem>
                                <SelectItem value="equity" class="whitespace-nowrap">Equity</SelectItem>
                                <SelectItem value="revenue" class="whitespace-nowrap">Revenue</SelectItem>
                                <SelectItem value="expense" class="whitespace-nowrap">Expense</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <Select v-model="statusFilter">
                            <SelectTrigger class="w-full sm:w-[180px]">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent class="min-w-[180px]">
                                <SelectItem value="__all__" class="whitespace-nowrap">All Statuses</SelectItem>
                                <SelectItem value="active" class="whitespace-nowrap">Active</SelectItem>
                                <SelectItem value="inactive" class="whitespace-nowrap">Inactive</SelectItem>
                                <SelectItem value="archived" class="whitespace-nowrap">Archived</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <Select v-model="withTrashedFilter">
                            <SelectTrigger class="w-full sm:w-[180px]">
                                <SelectValue placeholder="Filter accounts" />
                            </SelectTrigger>
                            <SelectContent class="min-w-[180px]">
                                <SelectItem value="none" class="whitespace-nowrap">Active Accounts</SelectItem>
                                <SelectItem value="only" class="whitespace-nowrap">Deleted Accounts</SelectItem>
                                <SelectItem value="all" class="whitespace-nowrap">All Accounts</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <TreeView
                    :accounts="props.accounts"
                    :on-delete-click="openDeleteDialog"
                    :on-restore-click="openRestoreDialog"
                    :on-force-delete-click="openForceDeleteDialog"
                />
            </div>
        </div>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Delete account</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        <span class="font-medium text-foreground">
                            {{ pendingDeleteAccount?.name ?? 'this account' }}
                        </span>
                        ? This action can be undone by restoring the account.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:space-x-0">
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancelDelete"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="pendingDeleteAction?.processing()"
                        @click="confirmDelete"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isRestoreDialogOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Restore account</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to restore
                        <span class="font-medium text-foreground">
                            {{ pendingDeleteAccount?.name ?? 'this account' }}
                        </span>
                        ? The account will be active again.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:space-x-0">
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancelRestore"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="default"
                        :disabled="pendingRestoreAction?.processing()"
                        @click="confirmRestore"
                    >
                        Restore
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isForceDeleteDialogOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Permanently delete account</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to permanently delete
                        <span class="font-medium text-foreground">
                            {{ pendingDeleteAccount?.name ?? 'this account' }}
                        </span>
                        ? This action cannot be undone and all data associated with this account will be permanently removed.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:space-x-0">
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancelForceDelete"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="pendingForceDeleteAction?.processing()"
                        @click="confirmForceDelete"
                    >
                        Permanently Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

