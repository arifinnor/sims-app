<script setup lang="ts">
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

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

interface ParentAccount {
    id: string;
    accountNumber: string;
    fullAccountNumber: string;
    name: string;
    type: string;
}

interface AccountFormData {
    id: string;
    accountNumber: string;
    fullAccountNumber: string;
    name: string;
    type: string;
    category: string | null;
    parentAccountId: string | null;
    balance: string;
    currency: string;
    status: string;
    description: string | null;
}

interface Props {
    account: AccountFormData;
    parentAccounts: ParentAccount[];
    hasChildren: boolean;
}

const props = defineProps<Props>();

const parentAccountId = ref<string>(props.account.parentAccountId || '__none__');
const accountType = ref<string>(props.account.type);
const accountStatus = ref<string>(props.account.status);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chart of Accounts',
        href: AccountController.index().url,
    },
    {
        title: props.account.name,
        href: AccountController.show.url(props.account.id),
    },
    {
        title: 'Edit',
        href: AccountController.edit.url(props.account.id),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${props.account.name}`" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="`Edit ${props.account.name}`"
                        description="Update the account details"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button
                        as-child
                        variant="secondary"
                        class="w-full md:w-auto"
                    >
                        <Link :href="AccountController.show.url(props.account.id)">
                            View account
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="ghost"
                        class="w-full md:w-auto"
                    >
                        <Link :href="AccountController.index().url">
                            Back to accounts
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <Form
                :action="AccountController.update.url(props.account.id)"
                method="put"
                class="contents"
                :options="{ preserveScroll: true }"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="account_number">Account Number</Label>
                        <Input
                            id="account_number"
                            name="account_number"
                            type="text"
                            required
                            :default-value="props.account.accountNumber"
                        />
                        <InputError :message="errors.account_number" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            :default-value="props.account.name"
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
                            name="category"
                            type="text"
                            :default-value="props.account.category || ''"
                            placeholder="e.g., current_asset, operating_expense"
                        />
                        <InputError :message="errors.category" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="parent_account_id">Parent Account</Label>
                        <input type="hidden" name="parent_account_id" :value="parentAccountId && parentAccountId !== '__none__' ? parentAccountId : ''" />
                        <Select
                            v-model="parentAccountId"
                            :disabled="props.hasChildren"
                        >
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
                        <p v-if="props.hasChildren" class="text-sm text-yellow-600 dark:text-yellow-400">
                            Cannot change parent account when account has children.
                        </p>
                        <p v-else class="text-sm text-muted-foreground">
                            Optional - select a parent account to create a hierarchical structure
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="balance">Balance</Label>
                        <Input
                            id="balance"
                            name="balance"
                            type="number"
                            step="0.01"
                            min="0"
                            :default-value="props.account.balance"
                        />
                        <InputError :message="errors.balance" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency">Currency</Label>
                        <Input
                            id="currency"
                            name="currency"
                            type="text"
                            :default-value="props.account.currency"
                            maxlength="3"
                        />
                        <InputError :message="errors.currency" />
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
                            :default-value="props.account.description || ''"
                            placeholder="Optional description"
                            rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <InputError :message="errors.description" />
                    </div>
                </CardContent>

                <CardFooter class="flex flex-col gap-3 md:flex-row md:items-center md:justify-end">
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-show="recentlySuccessful"
                            class="text-sm text-neutral-600 dark:text-neutral-300"
                        >
                            Changes saved.
                        </p>
                    </Transition>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="update-account-button"
                    >
                        Save changes
                    </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>

