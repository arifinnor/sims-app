<script setup lang="ts">
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { ref } from 'vue';

interface ParentAccount {
    id: string;
    accountNumber: string;
    fullAccountNumber: string;
    name: string;
    type: string;
}

interface Props {
    parentAccounts: ParentAccount[];
}

const props = defineProps<Props>();

const errors = ref<Record<string, string>>({});
const processing = ref(false);
const parentAccountId = ref<string>('__none__');
const accountType = ref<string>('');
const accountStatus = ref<string>('active');

const handleSubmit = (e: Event) => {
    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);
    const parentId = parentAccountId.value || formData.get('parent_account_id');
    const data: Record<string, any> = {
        account_number: formData.get('account_number'),
        name: formData.get('name'),
        type: accountType.value || formData.get('type'),
        category: formData.get('category') || null,
        parent_account_id: parentId && parentId !== '__none__' ? parentId : null,
        balance: formData.get('balance') ? parseFloat(formData.get('balance') as string) : null,
        currency: formData.get('currency') || 'IDR',
        status: accountStatus.value || formData.get('status') || 'active',
        description: formData.get('description') || null,
    };

    processing.value = true;
    router.post(AccountController.store.url(), data, {
        preserveScroll: true,
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>;
            processing.value = false;
        },
        onSuccess: () => {
            processing.value = false;
        },
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chart of Accounts',
        href: AccountController.index().url,
    },
    {
        title: 'Create account',
        href: AccountController.create().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create account" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading title="Create account" description="Add a new account to the chart of accounts" />
                </div>
                <Button as-child variant="secondary" class="w-full md:w-auto">
                    <Link :href="AccountController.index().url"> Cancel </Link>
                </Button>
            </CardHeader>
            <form :action="AccountController.store.url()" method="post" class="contents" @submit.prevent="handleSubmit">
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="account_number">Account Number</Label>
                        <Input
                            id="account_number"
                            type="text"
                            name="account_number"
                            required
                            placeholder="e.g., 1000, 1010"
                            autofocus
                        />
                        <InputError :message="errors.account_number" />
                        <p class="text-sm text-muted-foreground">
                            Unique identifier for this account (e.g., 1000, 1010, 1011)
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            required
                            placeholder="Account name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="type">Type</Label>
                        <input type="hidden" name="type" :value="accountType" required />
                        <Select v-model="accountType" required>
                            <SelectTrigger id="type">
                                <SelectValue placeholder="Select account type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="asset">Asset</SelectItem>
                                <SelectItem value="liability">Liability</SelectItem>
                                <SelectItem value="equity">Equity</SelectItem>
                                <SelectItem value="revenue">Revenue</SelectItem>
                                <SelectItem value="expense">Expense</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category">Category</Label>
                        <Input
                            id="category"
                            type="text"
                            name="category"
                            placeholder="e.g., current_asset, operating_expense"
                        />
                        <InputError :message="errors.category" />
                        <p class="text-sm text-muted-foreground">
                            Optional sub-classification (e.g., current_asset, fixed_asset, operating_expense)
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="parent_account_id">Parent Account</Label>
                        <input type="hidden" name="parent_account_id" :value="parentAccountId && parentAccountId !== '__none__' ? parentAccountId : ''" />
                        <Select v-model="parentAccountId">
                            <SelectTrigger id="parent_account_id">
                                <SelectValue placeholder="Select parent account (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">None (Root Account)</SelectItem>
                                <SelectItem
                                    v-for="parent in props.parentAccounts"
                                    :key="parent.id"
                                    :value="parent.id"
                                >
                                    {{ parent.fullAccountNumber }} - {{ parent.name }} ({{ parent.type }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.parent_account_id" />
                        <p class="text-sm text-muted-foreground">
                            Optional - select a parent account to create a hierarchical structure
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="balance">Balance</Label>
                        <Input
                            id="balance"
                            type="number"
                            name="balance"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        />
                        <InputError :message="errors.balance" />
                        <p class="text-sm text-muted-foreground">
                            Initial balance (defaults to 0.00)
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency">Currency</Label>
                        <Input
                            id="currency"
                            type="text"
                            name="currency"
                            placeholder="IDR"
                            value="IDR"
                            maxlength="3"
                        />
                        <InputError :message="errors.currency" />
                        <p class="text-sm text-muted-foreground">
                            Currency code (defaults to IDR)
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <input type="hidden" name="status" :value="accountStatus" />
                        <Select v-model="accountStatus">
                            <SelectTrigger id="status">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                                <SelectItem value="archived">Archived</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="Optional description"
                            rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <InputError :message="errors.description" />
                    </div>
                </CardContent>
                <CardFooter class="justify-end">
                    <Button type="submit" :disabled="processing" data-test="create-account-button">
                        <Spinner v-if="processing" />
                        Create account
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </AppLayout>
</template>

