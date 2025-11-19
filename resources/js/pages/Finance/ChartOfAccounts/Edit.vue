<script setup lang="ts">
import ChartOfAccountController from '@/actions/App/Http/Controllers/Finance/ChartOfAccountController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

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
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Spinner } from '@/components/ui/spinner';
import { ref, watch } from 'vue';

interface ChartOfAccount {
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
}

interface Props {
    account: ChartOfAccount;
    parentOptions: Array<{ id: string; code: string; name: string }>;
    categories: Array<{ id: string; name: string }>;
    accountTypes: string[];
    normalBalances: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chart of Accounts',
        href: ChartOfAccountController.index().url,
    },
    {
        title: props.account.name,
        href: ChartOfAccountController.show.url(props.account.id),
    },
    {
        title: 'Edit',
        href: ChartOfAccountController.edit.url(props.account.id),
    },
];

const accountType = ref<string>(props.account.account_type);
const normalBalance = ref<string>(props.account.normal_balance);

watch(accountType, (newType) => {
    // Auto-set normal balance based on account type
    if (newType === 'ASSET' || newType === 'EXPENSE') {
        normalBalance.value = 'DEBIT';
    } else if (newType === 'LIABILITY' || newType === 'EQUITY' || newType === 'REVENUE') {
        normalBalance.value = 'CREDIT';
    }
});
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
                        <Link :href="ChartOfAccountController.show.url(props.account.id)">
                            View Account
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="ghost"
                        class="w-full md:w-auto"
                    >
                        <Link :href="ChartOfAccountController.index().url">
                            Back to Accounts
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <Form
                v-bind="ChartOfAccountController.update.form(props.account.id)"
                class="contents"
                :options="{ preserveScroll: true }"
                v-slot="{ errors, processing }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="code">Account Code <span class="text-destructive">*</span></Label>
                        <Input
                            id="code"
                            type="text"
                            name="code"
                            required
                            :default-value="props.account.code"
                        />
                        <InputError :message="errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Account Name <span class="text-destructive">*</span></Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            required
                            :default-value="props.account.name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            name="description"
                            rows="3"
                            :default-value="props.account.description || ''"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category_id">Category</Label>
                        <Select name="category_id" :default-value="props.account.category_id ? String(props.account.category_id) : ''">
                            <SelectTrigger id="category_id">
                                <SelectValue placeholder="Select a category (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">None</SelectItem>
                                <SelectItem
                                    v-for="category in props.categories"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    {{ category.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.category_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="parent_id">Parent Account</Label>
                        <Select name="parent_id" :default-value="props.account.parent_id ? String(props.account.parent_id) : ''">
                            <SelectTrigger id="parent_id">
                                <SelectValue placeholder="Select a parent account (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">None (Root Level)</SelectItem>
                                <SelectItem
                                    v-for="parent in props.parentOptions"
                                    :key="parent.id"
                                    :value="String(parent.id)"
                                >
                                    {{ parent.code }} - {{ parent.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.parent_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="account_type">Account Type <span class="text-destructive">*</span></Label>
                        <Select v-model="accountType" name="account_type" required>
                            <SelectTrigger id="account_type">
                                <SelectValue placeholder="Select account type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="type in props.accountTypes"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ type }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.account_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="normal_balance">Normal Balance <span class="text-destructive">*</span></Label>
                        <Select v-model="normalBalance" name="normal_balance" required>
                            <SelectTrigger id="normal_balance">
                                <SelectValue placeholder="Select normal balance" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="balance in props.normalBalances"
                                    :key="balance"
                                    :value="balance"
                                >
                                    {{ balance }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.normal_balance" />
                    </div>

                    <div class="grid gap-4">
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_posting"
                                name="is_posting"
                                value="1"
                                :checked="props.account.is_posting"
                            />
                            <Label
                                for="is_posting"
                                class="text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                Posting Account
                            </Label>
                        </div>
                        <InputError :message="errors.is_posting" />
                    </div>

                    <div class="grid gap-4">
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_cash"
                                name="is_cash"
                                value="1"
                                :checked="props.account.is_cash"
                            />
                            <Label
                                for="is_cash"
                                class="text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                Cash Account
                            </Label>
                        </div>
                        <InputError :message="errors.is_cash" />
                    </div>

                    <div class="grid gap-4">
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_active"
                                name="is_active"
                                value="1"
                                :checked="props.account.is_active"
                            />
                            <Label
                                for="is_active"
                                class="text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                Active
                            </Label>
                        </div>
                        <InputError :message="errors.is_active" />
                    </div>
                </CardContent>

                <CardFooter class="flex flex-col gap-3 md:flex-row md:items-center md:justify-end">
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="w-full md:w-auto"
                    >
                        <Spinner
                            v-if="processing"
                            class="mr-2 h-4 w-4"
                        />
                        {{ processing ? 'Updating...' : 'Update Account' }}
                    </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>

